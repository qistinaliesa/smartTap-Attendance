<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();


        // Basic stats
        $totalStudents = DB::table('cards')->count();

        $present = DB::table('attendance')
            ->whereDate('date', $today)
            ->distinct('card_id')
            ->count('card_id');

        $absent = max(0, $totalStudents - $present);

        // Attendance by course (only if enrollments/courses exist)
        $attendanceByCourse = collect();
        if (
            Schema::hasTable('enrollments') &&
            Schema::hasTable('courses') &&
            Schema::hasColumn('enrollments', 'card_id') &&
            Schema::hasColumn('enrollments', 'course_id')
        ) {
            $attendanceByCourse = DB::table('attendance')
                ->join('cards', 'attendance.card_id', '=', 'cards.id')
                ->join('enrollments', 'cards.id', '=', 'enrollments.card_id')
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->select('courses.course_code', DB::raw('COUNT(DISTINCT attendance.card_id) as total'))
                ->whereDate('attendance.date', $today)
                ->groupBy('courses.course_code', 'courses.id')
                ->get();
        }

        // Recent attendance with course information and time-based status calculation
        $recentAttendance = $this->getRecentAttendanceWithCourse($today);

        return view('admin.home', compact(
            'totalStudents', 'present', 'absent', 'attendanceByCourse', 'recentAttendance'
        ));
    }

    /**
     * Get recent attendance with course information
     */
    private function getRecentAttendanceWithCourse($today)
    {
        $query = DB::table('attendance')
            ->join('cards', 'attendance.card_id', '=', 'cards.id')
            ->select('cards.name', 'cards.uid', 'attendance.date')
            ->whereDate('attendance.date', $today)
            ->orderBy('attendance.date', 'desc')
            ->limit(15);

        // Add course information if available
        if (
            Schema::hasTable('enrollments') &&
            Schema::hasTable('courses') &&
            Schema::hasColumn('enrollments', 'card_id') &&
            Schema::hasColumn('enrollments', 'course_id')
        ) {
            $query->leftJoin('enrollments', 'cards.id', '=', 'enrollments.card_id')
                  ->leftJoin('courses', 'enrollments.course_id', '=', 'courses.id')
                  ->addSelect('courses.course_code');
        }

        return $query->get()->map(function ($entry) {
            $time = Carbon::parse($entry->date);
            $hour = $time->hour;
            $minute = $time->minute;
            $timeInMinutes = $hour * 60 + $minute;

            // Define time ranges
            $earlyTime = 8 * 60 + 30; // 8:30 AM
            $onTime = 9 * 60; // 9:00 AM

            if ($timeInMinutes < $earlyTime) {
                $entry->status = 'Early';
                $entry->status_class = 'status-early';
            } elseif ($timeInMinutes <= $onTime) {
                $entry->status = 'On Time';
                $entry->status_class = 'status-ontime';
            } else {
                $entry->status = 'Late';
                $entry->status_class = 'status-late';
            }

            $entry->formatted_time = $time->format('h:i A');
            return $entry;
        });
    }

    /**
     * Get real-time dashboard data via AJAX
     */
    public function getRealtimeData()
    {
        $today = Carbon::today();

        // Basic stats
        $totalStudents = DB::table('cards')->count();
        $present = DB::table('attendance')
            ->whereDate('date', $today)
            ->distinct('card_id')
            ->count('card_id');
        $absent = max(0, $totalStudents - $present);

        // Calculate percentages
        $presentPercentage = $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0;
        $absentPercentage = $totalStudents > 0 ? round(($absent / $totalStudents) * 100, 1) : 0;

        // Time-based attendance stats
        $timeBasedStats = $this->getTimeBasedStats($today);

        // Course attendance data for chart
        $attendanceByCourse = $this->getCourseAttendanceData($today);

        return response()->json([
            'totalStudents' => $totalStudents,
            'present' => $present,
            'absent' => $absent,
            'presentPercentage' => $presentPercentage,
            'absentPercentage' => $absentPercentage,
            'timeBasedStats' => $timeBasedStats,
            'courseLabels' => $attendanceByCourse->pluck('course_code'),
            'courseData' => $attendanceByCourse->pluck('total'),
        ]);
    }

    /**
     * Get recent attendance data via AJAX
     */
    public function getRecentAttendance()
    {
        $today = Carbon::today();
        return response()->json($this->getRecentAttendanceWithCourse($today));
    }

    /**
     * Get course attendance data via AJAX
     */
    public function getCourseAttendanceData($today = null)
    {
        if (!$today) {
            $today = Carbon::today();
        }

        if (
            !Schema::hasTable('enrollments') ||
            !Schema::hasTable('courses') ||
            !Schema::hasColumn('enrollments', 'card_id') ||
            !Schema::hasColumn('enrollments', 'course_id')
        ) {
            return collect();
        }

        return DB::table('attendance')
            ->join('cards', 'attendance.card_id', '=', 'cards.id')
            ->join('enrollments', 'cards.id', '=', 'enrollments.card_id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select('courses.course_code', 'courses.id', DB::raw('COUNT(DISTINCT attendance.card_id) as total'))
            ->whereDate('attendance.date', $today)
            ->groupBy('courses.course_code', 'courses.id')
            ->get();
    }

    /**
     * Get course attendance via AJAX endpoint
     */
    public function getCourseAttendance()
{
    $today = Carbon::today();

    if (
        !Schema::hasTable('enrollments') ||
        !Schema::hasTable('courses') ||
        !Schema::hasColumn('enrollments', 'card_id') ||
        !Schema::hasColumn('enrollments', 'course_id')
    ) {
        // Return mock data if schema is incomplete
        return response()->json($this->getMockCourseAttendanceData());
    }

    try {
        $courseData = DB::table('courses')
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('cards', 'enrollments.card_id', '=', 'cards.id')
            ->leftJoin('attendance', function($join) use ($today) {
                $join->on('cards.id', '=', 'attendance.card_id')
                     ->whereDate('attendance.date', $today);
            })
            ->select(
                'courses.id',
                'courses.course_code',
                'courses.title',
                DB::raw('COUNT(DISTINCT enrollments.id) as enrolled'),
                DB::raw('COUNT(DISTINCT attendance.card_id) as total')
            )
            ->groupBy('courses.id', 'courses.course_code', 'courses.title')
            ->having('enrolled', '>', 0) // Only courses with enrollments
            ->orderBy('enrolled', 'desc')
            ->get();

        // Process the data to match frontend expectations
        $processedData = $courseData->map(function($course) {
            return (object)[
                'id' => $course->id,
                'course_code' => $course->course_code,
                'title' => $course->title ?? $course->course_code,
                'enrolled' => (int)$course->enrolled,
                'total' => (int)$course->total,
                'attendance_percentage' => $course->enrolled > 0 ?
                    round(($course->total / $course->enrolled) * 100, 1) : 0
            ];
        });

        \Log::info('Course Attendance Data:', [
            'count' => $processedData->count(),
            'data' => $processedData->toArray()
        ]);

        return response()->json($processedData->isEmpty() ?
            $this->getMockCourseAttendanceData() : $processedData->values());

    } catch (\Exception $e) {
        \Log::error('Error in getCourseAttendance:', ['error' => $e->getMessage()]);
        return response()->json($this->getMockCourseAttendanceData());
    }
}
// private function getMockCourseAttendanceData()
// {
//     $today = Carbon::today();

//     // Get actual attendance count for today
//     $totalAttendanceToday = DB::table('attendance')
//         ->whereDate('date', $today)
//         ->distinct('card_id')
//         ->count('card_id');

//     // Get total students
//     $totalStudents = DB::table('cards')->count();
//     if ($totalStudents == 0) $totalStudents = 100;

//     // Create mock course data
//     $mockCourses = [
//         ['code' => 'CS1235', 'title' => 'Introduction to Programming', 'enrolled' => 25],
//         ['code' => 'CS1113', 'title' => 'Computer Science Fundamentals', 'enrolled' => 22],

//     ];

//     return collect($mockCourses)->map(function($course, $index) use ($totalAttendanceToday, $totalStudents) {
//         // Calculate realistic attendance based on overall attendance rate
//         $overallRate = $totalStudents > 0 ? $totalAttendanceToday / $totalStudents : 0.7;

//         // Add some variation per course
//         $courseRate = $overallRate + (rand(-20, 20) / 100);
//         $courseRate = max(0.3, min(0.95, $courseRate)); // Keep between 30-95%

//         $present = (int)round($course['enrolled'] * $courseRate);

//         return (object)[
//             'id' => $index + 1,
//             'course_code' => $course['code'],
//             'title' => $course['title'],
//             'enrolled' => $course['enrolled'],
//             'total' => $present,
//             'attendance_percentage' => round(($present / $course['enrolled']) * 100, 1)
//         ];
//     });
// }

    /**
     * Get time-based attendance statistics
     */
    private function getTimeBasedStats($today)
    {
        $attendance = DB::table('attendance')
            ->whereDate('date', $today)
            ->get(['date']);

        $early = 0;
        $onTime = 0;
        $late = 0;

        foreach ($attendance as $entry) {
            $time = Carbon::parse($entry->date);
            $timeInMinutes = $time->hour * 60 + $time->minute;

            $earlyTime = 8 * 60 + 30; // 8:30 AM
            $onTimeLimit = 9 * 60; // 9:00 AM

            if ($timeInMinutes < $earlyTime) {
                $early++;
            } elseif ($timeInMinutes <= $onTimeLimit) {
                $onTime++;
            } else {
                $late++;
            }
        }

        return [
            'early' => $early,
            'onTime' => $onTime,
            'late' => $late,
        ];
    }

    /**
     * Get weekly attendance data for charts
     */
    public function getWeeklyAttendance()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyData = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);

            $present = DB::table('attendance')
                ->whereDate('date', $date)
                ->distinct('card_id')
                ->count('card_id');

            $totalStudents = DB::table('cards')->count();
            $absent = max(0, $totalStudents - $present);

            // Calculate late arrivals for this day
            $late = DB::table('attendance')
                ->whereDate('date', $date)
                ->whereTime('date', '>', '09:00:00')
                ->distinct('card_id')
                ->count('card_id');

            $weeklyData[] = [
                'day' => $days[$i],
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'date' => $date->format('Y-m-d')
            ];
        }

        return response()->json($weeklyData);
    }

    /**
     * Get attendance statistics for specific date range (for heatmap)
     */
    public function getAttendanceStats(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(30));
        $endDate = $request->input('end_date', Carbon::today());

        $stats = DB::table('attendance')
            ->selectRaw('
                DATE(date) as attendance_date,
                COUNT(DISTINCT card_id) as present,
                COUNT(CASE WHEN TIME(date) < "08:30:00" THEN 1 END) as early,
                COUNT(CASE WHEN TIME(date) BETWEEN "08:30:00" AND "09:00:00" THEN 1 END) as on_time,
                COUNT(CASE WHEN TIME(date) > "09:00:00" THEN 1 END) as late
            ')
            ->whereBetween(DB::raw('DATE(date)'), [$startDate, $endDate])
            ->groupBy('attendance_date')
            ->orderBy('attendance_date', 'desc')
            ->get();

        $totalStudents = DB::table('cards')->count();

        $stats = $stats->map(function ($stat) use ($totalStudents) {
            $stat->absent = max(0, $totalStudents - $stat->present);
            $stat->attendance_percentage = $totalStudents > 0 ? round(($stat->present / $totalStudents) * 100, 2) : 0;
            return $stat;
        });

        return response()->json($stats);
    }

    /**
     * Get top performing courses by attendance
     */
    public function getTopCourses()
    {
        if (
            !Schema::hasTable('enrollments') ||
            !Schema::hasTable('courses') ||
            !Schema::hasColumn('enrollments', 'card_id') ||
            !Schema::hasColumn('enrollments', 'course_id')
        ) {
            return response()->json([]);
        }

        $today = Carbon::today();

        $topCourses = DB::table('courses')
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('cards', 'enrollments.card_id', '=', 'cards.id')
            ->leftJoin('attendance', function($join) use ($today) {
                $join->on('cards.id', '=', 'attendance.card_id')
                     ->whereDate('attendance.date', $today);
            })
            ->select(
                'courses.course_code',
                'courses.title',
                DB::raw('COUNT(DISTINCT enrollments.card_id) as enrolled_students'),
                DB::raw('COUNT(DISTINCT attendance.card_id) as present_students'),
                DB::raw('ROUND(
                    CASE
                        WHEN COUNT(DISTINCT enrollments.card_id) > 0
                        THEN (COUNT(DISTINCT attendance.card_id) * 100.0 / COUNT(DISTINCT enrollments.card_id))
                        ELSE 0
                    END, 2
                ) as attendance_percentage')
            )
            ->groupBy('courses.id', 'courses.course_code', 'courses.title')
            ->having('enrolled_students', '>', 0)
            ->orderBy('attendance_percentage', 'desc')
            ->limit(5)
            ->get();

        return response()->json($topCourses);
    }

    /**
     * Get monthly attendance comparison
     */
    public function getMonthlyComparison()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $totalStudents = DB::table('cards')->count();

        // Current month stats
        $currentStats = DB::table('attendance')
            ->whereDate('date', '>=', $currentMonth)
            ->selectRaw('
                COUNT(DISTINCT card_id, DATE(date)) as total_present_days,
                COUNT(DISTINCT DATE(date)) as days_with_attendance,
                COUNT(CASE WHEN TIME(date) > "09:00:00" THEN 1 END) as late_arrivals
            ')
            ->first();

        // Last month stats
        $lastStats = DB::table('attendance')
            ->whereDate('date', '>=', $lastMonth)
            ->whereDate('date', '<', $currentMonth)
            ->selectRaw('
                COUNT(DISTINCT card_id, DATE(date)) as total_present_days,
                COUNT(DISTINCT DATE(date)) as days_with_attendance,
                COUNT(CASE WHEN TIME(date) > "09:00:00" THEN 1 END) as late_arrivals
            ')
            ->first();

        $currentAverage = $currentStats->days_with_attendance > 0
            ? round($currentStats->total_present_days / $currentStats->days_with_attendance, 2)
            : 0;

        $lastAverage = $lastStats->days_with_attendance > 0
            ? round($lastStats->total_present_days / $lastStats->days_with_attendance, 2)
            : 0;

        $improvement = $lastAverage > 0
            ? round((($currentAverage - $lastAverage) / $lastAverage) * 100, 2)
            : 0;

        return response()->json([
            'current_month' => [
                'average_attendance' => $currentAverage,
                'late_arrivals' => $currentStats->late_arrivals,
                'attendance_days' => $currentStats->days_with_attendance
            ],
            'last_month' => [
                'average_attendance' => $lastAverage,
                'late_arrivals' => $lastStats->late_arrivals,
                'attendance_days' => $lastStats->days_with_attendance
            ],
            'improvement_percentage' => $improvement
        ]);
    }

    /**
     * Get attendance patterns by day of week
     */
    public function getAttendancePatterns()
    {
        $patterns = DB::table('attendance')
            ->selectRaw('
                DAYNAME(date) as day_of_week,
                DAYOFWEEK(date) as day_number,
                AVG(HOUR(date)) as average_arrival_hour,
                COUNT(DISTINCT card_id) as total_students,
                COUNT(CASE WHEN TIME(date) > "09:00:00" THEN 1 END) as late_count
            ')
            ->whereDate('date', '>=', Carbon::now()->subDays(30))
            ->groupBy('day_of_week', 'day_number')
            ->orderBy('day_number')
            ->get();

        return response()->json($patterns);
    }

    /**
     * Get student attendance streaks
     */
    public function getAttendanceStreaks()
    {
        // This is a complex query - simplified version
        $streaks = DB::table('cards')
            ->leftJoin('attendance', function($join) {
                $join->on('cards.id', '=', 'attendance.card_id')
                     ->whereDate('attendance.date', '>=', Carbon::now()->subDays(7));
            })
            ->select(
                'cards.name',
                'cards.uid',
                DB::raw('COUNT(DISTINCT DATE(attendance.date)) as consecutive_days')
            )
            ->groupBy('cards.id', 'cards.name', 'cards.uid')
            ->having('consecutive_days', '>', 0)
            ->orderBy('consecutive_days', 'desc')
            ->limit(10)
            ->get();

        return response()->json($streaks);
    }

    /**
     * Get real-time attendance notifications
     */
    public function getRealtimeNotifications()
    {
        $latestAttendance = DB::table('attendance')
            ->join('cards', 'attendance.card_id', '=', 'cards.id')
            ->select('cards.name', 'attendance.date', 'cards.uid')
            ->where('attendance.date', '>=', Carbon::now()->subMinutes(5))
            ->orderBy('attendance.date', 'desc')
            ->get()
            ->map(function ($entry) {
                $time = Carbon::parse($entry->date);
                $timeInMinutes = $time->hour * 60 + $time->minute;
                $earlyTime = 8 * 60 + 30; // 8:30 AM
                $onTime = 9 * 60; // 9:00 AM

                if ($timeInMinutes < $earlyTime) {
                    $status = 'Early';
                } elseif ($timeInMinutes <= $onTime) {
                    $status = 'On Time';
                } else {
                    $status = 'Late';
                }

                $entry->status = $status;
                $entry->time_ago = $time->diffForHumans();
                return $entry;
            });

        return response()->json($latestAttendance);
    }

    /**
     * Get course performance analytics
     */
    public function getCoursePerformanceAnalytics()
    {
        if (
            !Schema::hasTable('enrollments') ||
            !Schema::hasTable('courses') ||
            !Schema::hasColumn('enrollments', 'card_id') ||
            !Schema::hasColumn('enrollments', 'course_id')
        ) {
            return response()->json([]);
        }

        $analytics = DB::table('courses')
            ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('cards', 'enrollments.card_id', '=', 'cards.id')
            ->leftJoin('attendance', function($join) {
                $join->on('cards.id', '=', 'attendance.card_id')
                     ->whereDate('attendance.date', '>=', Carbon::now()->subDays(7));
            })
            ->select(
                'courses.course_code',
                'courses.title',
                DB::raw('COUNT(DISTINCT enrollments.card_id) as enrolled_students'),
                DB::raw('COUNT(DISTINCT attendance.card_id, DATE(attendance.date)) as total_attendance_records'),
                DB::raw('COUNT(DISTINCT DATE(attendance.date)) as days_with_attendance'),
                DB::raw('AVG(TIME_TO_SEC(TIME(attendance.date))) as avg_arrival_time_seconds'),
                DB::raw('COUNT(CASE WHEN TIME(attendance.date) > "09:00:00" THEN 1 END) as late_arrivals')
            )
            ->groupBy('courses.id', 'courses.course_code', 'courses.title')
            ->having('enrolled_students', '>', 0)
            ->get()
            ->map(function ($course) {
                // Calculate average attendance percentage
                $course->avg_attendance_percentage = $course->days_with_attendance > 0 && $course->enrolled_students > 0
                    ? round(($course->total_attendance_records / ($course->days_with_attendance * $course->enrolled_students)) * 100, 2)
                    : 0;

                // Convert average arrival time to readable format
                $avgHours = floor($course->avg_arrival_time_seconds / 3600);
                $avgMinutes = floor(($course->avg_arrival_time_seconds % 3600) / 60);
                $course->avg_arrival_time = sprintf('%02d:%02d', $avgHours, $avgMinutes);

                // Calculate punctuality rate
                $totalAttendance = $course->total_attendance_records;
                $course->punctuality_rate = $totalAttendance > 0
                    ? round((($totalAttendance - $course->late_arrivals) / $totalAttendance) * 100, 2)
                    : 0;

                return $course;
            });

        return response()->json($analytics);
    }

    /**
     * Export attendance data
     */
    public function exportAttendanceData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(30));
        $endDate = $request->input('end_date', Carbon::today());

        $attendanceData = DB::table('attendance')
            ->join('cards', 'attendance.card_id', '=', 'cards.id')
            ->select(
                'cards.name',
                'cards.uid',
                'attendance.date',
                DB::raw('DATE(attendance.date) as attendance_date'),
                DB::raw('TIME(attendance.date) as attendance_time'),
                DB::raw('CASE
                    WHEN TIME(attendance.date) < "08:30:00" THEN "Early"
                    WHEN TIME(attendance.date) <= "09:00:00" THEN "On Time"
                    ELSE "Late"
                END as status')
            )
            ->whereBetween(DB::raw('DATE(attendance.date)'), [$startDate, $endDate])
            ->orderBy('attendance.date', 'desc')
            ->get();

        // Add course information if available
        if (
            Schema::hasTable('enrollments') &&
            Schema::hasTable('courses') &&
            Schema::hasColumn('enrollments', 'card_id') &&
            Schema::hasColumn('enrollments', 'course_id')
        ) {
            $attendanceData = DB::table('attendance')
                ->join('cards', 'attendance.card_id', '=', 'cards.id')
                ->leftJoin('enrollments', 'cards.id', '=', 'enrollments.card_id')
                ->leftJoin('courses', 'enrollments.course_id', '=', 'courses.id')
                ->select(
                    'cards.name',
                    'cards.uid',
                    'attendance.date',
                    'courses.course_code',
                    DB::raw('DATE(attendance.date) as attendance_date'),
                    DB::raw('TIME(attendance.date) as attendance_time'),
                    DB::raw('CASE
                        WHEN TIME(attendance.date) < "08:30:00" THEN "Early"
                        WHEN TIME(attendance.date) <= "09:00:00" THEN "On Time"
                        ELSE "Late"
                    END as status')
                )
                ->whereBetween(DB::raw('DATE(attendance.date)'), [$startDate, $endDate])
                ->orderBy('attendance.date', 'desc')
                ->get();
        }

        return response()->json($attendanceData);
    }

    /**
     * Get dashboard summary for mobile/API
     */
    public function getDashboardSummary()
    {
        $today = Carbon::today();
        $totalStudents = DB::table('cards')->count();

        $present = DB::table('attendance')
            ->whereDate('date', $today)
            ->distinct('card_id')
            ->count('card_id');

        $timeBasedStats = $this->getTimeBasedStats($today);

        // Get week comparison
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $thisWeekStart = Carbon::now()->startOfWeek();

        $lastWeekAvg = DB::table('attendance')
            ->whereBetween(DB::raw('DATE(date)'), [$lastWeekStart->format('Y-m-d'), $lastWeekEnd->format('Y-m-d')])
            ->selectRaw('COUNT(DISTINCT card_id) / COUNT(DISTINCT DATE(date)) as avg_attendance')
            ->value('avg_attendance') ?? 0;

        $thisWeekAvg = DB::table('attendance')
            ->whereBetween(DB::raw('DATE(date)'), [$thisWeekStart->format('Y-m-d'), $today->format('Y-m-d')])
            ->selectRaw('COUNT(DISTINCT card_id) / COUNT(DISTINCT DATE(date)) as avg_attendance')
            ->value('avg_attendance') ?? 0;

        $weekTrend = $lastWeekAvg > 0 ? round((($thisWeekAvg - $lastWeekAvg) / $lastWeekAvg) * 100, 1) : 0;

        return response()->json([
            'summary' => [
                'total_students' => $totalStudents,
                'present_today' => $present,
                'absent_today' => max(0, $totalStudents - $present),
                'attendance_rate' => $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0,
                'week_trend' => $weekTrend,
                'time_based_stats' => $timeBasedStats
            ],
            'quick_stats' => [
                'early_arrivals' => $timeBasedStats['early'],
                'on_time' => $timeBasedStats['onTime'],
                'late_arrivals' => $timeBasedStats['late']
            ]
        ]);
    }
    public function getCourseEnrollment()
{
    $today = Carbon::today();
    $enrollmentData = $this->getCourseEnrollmentData($today);

    \Log::info('Course Enrollment API Response:', [
        'count' => $enrollmentData->count(),
        'data' => $enrollmentData->toArray()
    ]);

    return response()->json($enrollmentData->values());
}

/**
 * Get course enrollment data with attendance information
 */
public function getCourseEnrollmentData($today = null)
{
    if (!$today) {
        $today = Carbon::today();
    }

    // Check if we have the required tables and columns
    $hasEnrollments = Schema::hasTable('enrollments') &&
                     Schema::hasColumn('enrollments', 'course_id');

    $hasCourses = Schema::hasTable('courses');
    $hasCards = Schema::hasTable('cards');

    \Log::info('Enrollment Schema Check:', [
        'has_enrollments' => $hasEnrollments,
        'has_courses' => $hasCourses,
        'has_cards' => $hasCards,
        'date' => $today->format('Y-m-d')
    ]);

    if (!$hasEnrollments || !$hasCourses) {
        \Log::info('Using mock enrollment data due to missing schema');
        return $this->getMockEnrollmentData($today);
    }

    try {
        // Check if enrollments has card_id column
        $hasCardIdColumn = Schema::hasColumn('enrollments', 'card_id');

        if ($hasCardIdColumn) {
            // Full query with proper relationships
            $enrollmentData = DB::table('courses')
                ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->leftJoin('cards', 'enrollments.card_id', '=', 'cards.id')
                ->leftJoin('attendance', function($join) use ($today) {
                    $join->on('cards.id', '=', 'attendance.card_id')
                         ->whereDate('attendance.date', $today);
                })
                ->select(
                    'courses.id',
                    'courses.course_code',
                    'courses.title',
                    DB::raw('COUNT(DISTINCT enrollments.id) as enrolled'),
                    DB::raw('COUNT(DISTINCT attendance.card_id) as total')
                )
                ->groupBy('courses.id', 'courses.course_code', 'courses.title')
                ->orderBy('enrolled', 'desc')
                ->get();
        } else {
            // Fallback query without card_id relationship
            $enrollmentData = DB::table('courses')
                ->leftJoin('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->select(
                    'courses.id',
                    'courses.course_code',
                    'courses.title',
                    DB::raw('COUNT(enrollments.id) as enrolled')
                )
                ->groupBy('courses.id', 'courses.course_code', 'courses.title')
                ->orderBy('enrolled', 'desc')
                ->get()
                ->map(function($course) use ($today) {
                    // Estimate attendance based on overall attendance rate
                    $totalAttendanceToday = DB::table('attendance')
                        ->whereDate('date', $today)
                        ->distinct('card_id')
                        ->count('card_id');

                    $totalStudents = DB::table('cards')->count();
                    $attendanceRate = $totalStudents > 0 ? $totalAttendanceToday / $totalStudents : 0.7;

                    $course->total = (int)round($course->enrolled * $attendanceRate);
                    return $course;
                });
        }

        // Process the data
        $processedData = $enrollmentData->map(function($course) {
            return (object)[
                'id' => $course->id,
                'course_code' => $course->course_code,
                'title' => $course->title ?? $course->course_code,
                'enrolled' => (int)$course->enrolled,
                'total' => (int)($course->total ?? 0),
                'attendance_percentage' => $course->enrolled > 0 ?
                    round((($course->total ?? 0) / $course->enrolled) * 100, 1) : 0
            ];
        })
        ->filter(function($course) {
            return $course->enrolled > 0; // Only show courses with enrollments
        })
        ->values();

        \Log::info('Processed enrollment data:', ['count' => $processedData->count()]);

        return $processedData->isEmpty() ? $this->getMockEnrollmentData($today) : $processedData;

    } catch (\Exception $e) {
        \Log::error('Error fetching enrollment data:', ['error' => $e->getMessage()]);
        return $this->getMockEnrollmentData($today);
    }
}

/**
 * Generate mock enrollment data for demonstration
 */
private function getMockEnrollmentData($today)
{
    \Log::info('Generating mock enrollment data');

    // Get real courses if they exist
    $realCourses = collect([]);
    try {
        if (Schema::hasTable('courses')) {
            $realCourses = DB::table('courses')
                ->select('id', 'course_code', 'title')
                ->limit(8)
                ->get();
        }
    } catch (\Exception $e) {
        \Log::warning('Could not fetch real courses for enrollment:', ['error' => $e->getMessage()]);
    }

    // Use real courses or create mock ones
    if ($realCourses->isNotEmpty()) {
        $courses = $realCourses;
    } else {
        $courses = collect([
            (object)['id' => 1, 'course_code' => 'CS101', 'title' => 'Introduction to Computing'],
            (object)['id' => 2, 'course_code' => 'CS201', 'title' => 'Data Structures & Algorithms'],
            (object)['id' => 3, 'course_code' => 'CS301', 'title' => 'Database Systems'],
            (object)['id' => 4, 'course_code' => 'CS401', 'title' => 'Software Engineering'],
            (object)['id' => 5, 'course_code' => 'MATH201', 'title' => 'Discrete Mathematics'],
            (object)['id' => 6, 'course_code' => 'ENG102', 'title' => 'Technical Writing'],
            (object)['id' => 7, 'course_code' => 'PHYS201', 'title' => 'Physics for CS'],
            (object)['id' => 8, 'course_code' => 'STAT301', 'title' => 'Statistics & Probability'],
        ]);
    }

    // Get total students for realistic distribution
    $totalStudents = DB::table('cards')->count();
    if ($totalStudents == 0) {
        $totalStudents = 150; // Default for demo
    }

    // Check for any attendance today
    $attendanceToday = DB::table('attendance')
        ->whereDate('date', $today)
        ->distinct('card_id')
        ->count('card_id');

    return $courses->map(function($course, $index) use ($totalStudents, $attendanceToday, $today) {
        // Generate realistic enrollment numbers
        $baseEnrollment = $totalStudents / 6; // Average enrollment
        $variation = rand(-30, 50); // Add some variation
        $enrolled = max(10, min($totalStudents, (int)($baseEnrollment + $variation)));

        // Make some courses more popular
        $popularCourses = ['CS101', 'CS201', 'CS301'];
        if (in_array($course->course_code, $popularCourses)) {
            $enrolled = (int)($enrolled * 1.3); // 30% more popular
        }

        $enrolled = min($enrolled, $totalStudents); // Cap at total students

        // Generate attendance for today (60-95% of enrolled)
        $attendanceRate = rand(60, 95) / 100;

        // Factor in day of week
        $dayOfWeek = $today->dayOfWeek;
        if ($dayOfWeek == Carbon::MONDAY) {
            $attendanceRate *= 0.85; // Lower on Monday
        } elseif ($dayOfWeek == Carbon::FRIDAY) {
            $attendanceRate *= 0.80; // Lower on Friday
        }

        $present = (int)round($enrolled * $attendanceRate);
        $present = max(0, min($present, $enrolled, $attendanceToday)); // Realistic bounds

        return (object)[
            'id' => $course->id,
            'course_code' => $course->course_code,
            'title' => $course->title ?? $course->course_code,
            'enrolled' => $enrolled,
            'total' => $present,
            'attendance_percentage' => $enrolled > 0 ?
                round(($present / $enrolled) * 100, 1) : 0,
            'is_mock' => true
        ];
    })
    ->sortByDesc('enrolled')
    ->values();
}
}






