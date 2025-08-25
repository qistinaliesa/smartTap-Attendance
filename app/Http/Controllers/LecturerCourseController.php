<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Card;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LecturerCourseController extends Controller
{
    /**
     * Display courses assigned to the authenticated lecturer
     */
    public function index()
    {
        // Get the authenticated lecturer
        $lecturer = Auth::guard('lecturer')->user();

        if (!$lecturer) {
            return redirect('/login')->with('error', 'Please log in as a lecturer.');
        }

        // Get only courses assigned to this lecturer
        $courses = Course::where('lecturer_id', $lecturer->id)
                        ->with('lecturer')
                        ->get();

        return view('lecturer.courses', compact('courses', 'lecturer'));
    }

    /**
     * Show details of a specific course and enrolled students with attendance percentages
     */
  public function show(Course $course)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check if this course belongs to the authenticated lecturer
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to view this course.');
        }

        // Get enrolled students with their card information
        $enrolledStudents = Enrollment::where('course_id', $course->id)
                                    ->with(['card' => function($query) {
                                        $query->select('id', 'uid', 'name', 'matric_id');
                                    }])
                                    ->get();

        // Calculate attendance statistics
        $studentsWithAttendance = [];
        $totalWarnings = 0;
        $totalAttendanceSum = 0;

        // Get the total number of unique dates where attendance was taken for this course
        $enrolledCardIds = $enrolledStudents->pluck('card.id')->filter();
        $totalClasses = 0;

        if ($enrolledCardIds->isNotEmpty()) {
            $totalClasses = Attendance::whereIn('card_id', $enrolledCardIds)
                ->distinct('date')
                ->count('date');
        }

        // If no classes have been held yet, set to 1 to avoid division by zero
        if ($totalClasses == 0) {
            $totalClasses = 1;
        }

        foreach ($enrolledStudents as $index => $enrollment) {
            $attendanceData = [
                'enrollment' => $enrollment,
                'index' => $index + 1,
                'attendance_count' => 0,
                'attendance_percentage' => 0,
                'absences' => $totalClasses,
                'status' => 'critical',
                'has_warning' => $totalClasses > 3 // NEW: Warning based on absences > 3
            ];

            if ($enrollment->card) {
                // Get attendance count for this student
                $attendanceCount = Attendance::where('card_id', $enrollment->card->id)
                    ->distinct('date')
                    ->count('date');

                // Calculate attendance percentage
                $attendancePercentage = ($attendanceCount / $totalClasses) * 100;
                $absences = $totalClasses - $attendanceCount;

                // Determine status (for progress bar colors - keep percentage-based)
                $status = 'critical';
                if ($attendancePercentage >= 75) {
                    $status = 'good';
                } elseif ($attendancePercentage >= 50) {
                    $status = 'cautious';
                }

                // NEW LOGIC: Warning based on absences only (more than 3)
                $hasWarning = $absences > 3;

                if ($hasWarning) {
                    $totalWarnings++;
                }

                $attendanceData = [
                    'enrollment' => $enrollment,
                    'index' => $index + 1,
                    'attendance_count' => $attendanceCount,
                    'attendance_percentage' => round($attendancePercentage, 1),
                    'absences' => $absences,
                    'status' => $status,
                    'has_warning' => $hasWarning // Now purely based on absences > 3
                ];

                $totalAttendanceSum += $attendancePercentage;
            } else {
                // Student without card - count as warning only if would have > 3 absences
                if ($totalClasses > 3) {
                    $totalWarnings++;
                }
            }

            $studentsWithAttendance[] = $attendanceData;
        }

        // Calculate average attendance
        $averageAttendance = $enrolledStudents->count() > 0 ?
            round($totalAttendanceSum / $enrolledStudents->count(), 1) : 0;

        // Sort by attendance percentage (lowest first to highlight problems)
        usort($studentsWithAttendance, function($a, $b) {
            return $a['attendance_percentage'] <=> $b['attendance_percentage'];
        });

        $attendanceStats = [
            'total_students' => $enrolledStudents->count(),
            'average_attendance' => $averageAttendance,
            'total_warnings' => $totalWarnings, // Now counts students with > 3 absences
            'total_classes' => $totalClasses
        ];

        return view('lecturer.course-students', compact(
            'course',
            'lecturer',
            'studentsWithAttendance',
            'attendanceStats'
        ));
    }

    // ... rest of your existing methods remain the same ...

    /**
     * Show attendance for a specific course and date (existing method - kept for compatibility)
     */
    public function showAttendance(Course $course, Request $request)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check if this course belongs to the authenticated lecturer
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to view this course attendance.');
        }

        // Get the selected date from the request, default to today
        $selectedDate = $request->get('date', Carbon::today()->toDateString());

        // Validate date format
        try {
            $date = Carbon::createFromFormat('Y-m-d', $selectedDate);
        } catch (\Exception $e) {
            $date = Carbon::today();
            $selectedDate = $date->toDateString();
        }

        // Get all students enrolled in this course
        $enrolledStudents = Enrollment::where('course_id', $course->id)
                                    ->with(['card' => function($query) {
                                        $query->select('id', 'uid', 'name', 'matric_id');
                                    }])
                                    ->get();

        // Get the card IDs of enrolled students
        $enrolledCardIds = $enrolledStudents->pluck('card.id')->filter();

        // Get attendance records for the selected date for only enrolled students
        $attendances = Attendance::whereIn('card_id', $enrolledCardIds)
                                ->where('date', $selectedDate)
                                ->with('card')
                                ->get();

        // Format attendance data
        $formattedAttendances = $attendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'name' => $attendance->card->name,
                'matric_id' => $attendance->card->matric_id,
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out,
            ];
        });

        // Get total number of enrolled students for context
        $totalStudents = $enrolledStudents->count();

        return view('lecturer.course-attendance', compact(
            'course',
            'formattedAttendances',
            'totalStudents',
            'selectedDate',
            'lecturer'
        ));
    }

    /**
     * NEW: Show the take attendance page for a specific course and date
     */
    public function takeAttendance(Request $request, Course $course)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check if this course belongs to the authenticated lecturer
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to take attendance for this course.');
        }

        // Validate the date parameter
        $date = $request->input('date');
        if (!$date) {
            return redirect()->back()->with('error', 'Date is required to take attendance.');
        }

        // Validate date format
        try {
            $attendanceDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date format.');
        }

        // Get all students enrolled in this course
        $enrolledStudents = Enrollment::where('course_id', $course->id)
                                    ->with(['card' => function($query) {
                                        $query->select('id', 'uid', 'name', 'matric_id');
                                    }])
                                    ->get();

        // Get the card IDs of enrolled students
        $enrolledCardIds = $enrolledStudents->pluck('card.id')->filter();

        // Get existing attendance records for this date and course (only for enrolled students)
        $existingAttendances = Attendance::whereIn('card_id', $enrolledCardIds)
                                        ->where('date', $date)
                                        ->with('card')
                                        ->get();

        // Format the attendance data for display (using your existing attendance view structure)
        $formattedAttendances = $existingAttendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'name' => $attendance->card->name ?? 'N/A',
                'matric_id' => $attendance->card->matric_id ?? 'N/A',
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out ?? null,
                'card_uid' => $attendance->card->uid ?? 'N/A'
            ];
        });

        // Calculate totals
        $totalStudents = $enrolledStudents->count();
        $presentCount = $formattedAttendances->count();

        // Return to your existing attendance view with the formatted data
        // Update the view path to match your file structure
        return view('lecturer.take-attendance', compact(
            'formattedAttendances',
            'course',
            'totalStudents'
        ));
    }
    /**
     * NEW: Show attendance overview/statistics for a course (like your Figma design)
     */
    public function showOverview($courseId)
    {
        $course = Course::findOrFail($courseId);
        $lecturer = Auth::guard('lecturer')->user();

        // Check if this course belongs to the authenticated lecturer
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to view this course overview.');
        }

        // Get all enrollments for this course
        $enrollments = Enrollment::where('course_id', $courseId)
            ->with('card')
            ->get();

        $totalStudents = $enrollments->count();

        if ($totalStudents == 0) {
            return view('lecturer.course-overview', [
                'course' => $course,
                'studentsData' => [],
                'totalStudents' => 0,
                'averageAttendance' => 0,
                'warnings' => 0
            ]);
        }

        // Calculate attendance statistics for each student
        $studentsData = [];
        $totalAttendanceSum = 0;
        $warnings = 0;

        // Get the total number of classes (unique dates where attendance was taken)
        $totalClasses = Attendance::where('course_id', $courseId)
            ->distinct('date')
            ->count();

        // If no classes have been held yet, set a default or use enrollment date range
        if ($totalClasses == 0) {
            $totalClasses = 1; // Avoid division by zero
        }

        foreach ($enrollments as $index => $enrollment) {
            if (!$enrollment->card) {
                continue; // Skip if no card associated
            }

            // Get total attendance records for this student in this course
            $attendanceCount = Attendance::where('course_id', $courseId)
                ->where('card_id', $enrollment->card->id)
                ->distinct('date') // Count unique dates only
                ->count();

            // Calculate attendance percentage
            $attendancePercentage = ($attendanceCount / $totalClasses) * 100;

            // Count absences
            $absences = $totalClasses - $attendanceCount;

            // Determine status and warning
            $status = 'good';
            $hasWarning = false;

            if ($attendancePercentage < 50) {
                $status = 'critical';
                $hasWarning = true;
                $warnings++;
            } elseif ($attendancePercentage < 75) {
                $status = 'cautious';
                $hasWarning = true;
            }

            $studentsData[] = [
                'no' => $index + 1,
                'name' => $enrollment->card->name,
                'matric_id' => $enrollment->card->matric_id,
                'attendance_percentage' => round($attendancePercentage, 1),
                'absences' => $absences,
                'status' => $status,
                'has_warning' => $hasWarning
            ];

            $totalAttendanceSum += $attendancePercentage;
        }

        // Calculate average attendance
        $averageAttendance = $totalStudents > 0 ? round($totalAttendanceSum / $totalStudents, 1) : 0;

        // Sort students by attendance percentage (lowest first to highlight problems)
        usort($studentsData, function($a, $b) {
            return $a['attendance_percentage'] <=> $b['attendance_percentage'];
        });

        return view('lecturer.course-overview', compact(
            'course',
            'studentsData',
            'totalStudents',
            'averageAttendance',
            'warnings'
        ));
    }

    /**
     * NEW: Show attendance history for a course (optional - for viewing past records)
     */
    public function showAttendanceHistory(Request $request, Course $course)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check authorization
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to view this course attendance history.');
        }

        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Get enrolled students
        $enrolledStudents = Enrollment::where('course_id', $course->id)
                                    ->with(['card' => function($query) {
                                        $query->select('id', 'uid', 'name', 'matric_id');
                                    }])
                                    ->get();

        $enrolledCardIds = $enrolledStudents->pluck('card.id')->filter();

        // Get attendance records within date range
        $attendances = Attendance::whereIn('card_id', $enrolledCardIds)
                                ->whereBetween('date', [$startDate, $endDate])
                                ->with('card')
                                ->orderBy('date', 'desc')
                                ->orderBy('time_in', 'desc')
                                ->get();

        $formattedAttendances = $attendances->map(function ($attendance) {
            return [
                'name' => $attendance->card->name ?? 'N/A',
                'matric_id' => $attendance->card->matric_id ?? 'N/A',
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out ?? null
            ];
        });

        return view('attendance_history', compact('formattedAttendances', 'course', 'startDate', 'endDate'));
    }

    /**
     * NEW: Show attendance records for a specific student
     */
    public function showStudentAttendance(Course $course, $enrollmentId)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check authorization
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to view this student attendance.');
        }

        $enrollment = Enrollment::where('course_id', $course->id)
                                ->where('id', $enrollmentId)
                                ->with('card')
                                ->firstOrFail();

        $attendances = Attendance::where('card_id', $enrollment->card->id)
                                ->with('card')
                                ->orderBy('date', 'desc')
                                ->orderBy('time_in', 'desc')
                                ->take(50) // Limit to last 50 records
                                ->get();

        $formattedAttendances = $attendances->map(function ($attendance) {
            return [
                'name' => $attendance->card->name ?? 'N/A',
                'matric_id' => $attendance->card->matric_id ?? 'N/A',
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out ?? null
            ];
        });

        return view('student_attendance', compact('formattedAttendances', 'course', 'enrollment'));
    }
}
