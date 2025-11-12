<?php

namespace App\Http\Controllers;
use PDF; // Add this line
use Barryvdh\DomPDF\Facade\Pdf as PDFService;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Card;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalCertificate;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AttendanceWarningMail;
use Illuminate\Support\Facades\Log;  // ✅ FIXED: Already imported
use Illuminate\Support\Facades\DB;   // ✅ FIXED: Added missing import
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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

    // FIXED: Get all enrolled card IDs first
    $enrolledCardIds = $enrolledStudents->pluck('card.id')->filter();

    // FIXED: Calculate total classes based on ENROLLED STUDENTS' attendance dates only
    // This ensures we only count dates where at least one enrolled student attended
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

    // FIXED: Get all class dates for this course (based on enrolled students)
    $allClassDates = [];
    if ($enrolledCardIds->isNotEmpty()) {
        $allClassDates = Attendance::whereIn('card_id', $enrolledCardIds)
            ->distinct('date')
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();
    }

    // Calculate attendance statistics
    $studentsWithAttendance = [];
    $totalWarnings = 0;
    $totalAttendanceSum = 0;

    foreach ($enrolledStudents as $index => $enrollment) {
        $attendanceData = [
            'enrollment' => $enrollment,
            'index' => $index + 1,
            'attendance_count' => 0,
            'attendance_percentage' => 0,
            'absences' => $totalClasses,
            'status' => 'critical',
            'has_warning' => $totalClasses > 3,
            'total_classes' => $totalClasses, // ADDED: Store total classes for this student
            'class_dates' => $allClassDates   // ADDED: Store class dates for reference
        ];

        if ($enrollment->card) {
            // FIXED: Get attendance count for this student (only for class dates that exist)
            $attendanceCount = 0;
            if (!empty($allClassDates)) {
                $attendanceCount = Attendance::where('card_id', $enrollment->card->id)
                    ->whereIn('date', $allClassDates)
                    ->distinct('date')
                    ->count('date');
            }

            // Calculate attendance percentage
            $attendancePercentage = ($attendanceCount / $totalClasses) * 100;
            $absences = $totalClasses - $attendanceCount;

            // Determine status (for progress bar colors)
            $status = 'critical';
            if ($attendancePercentage >= 75) {
                $status = 'good';
            } elseif ($attendancePercentage >= 50) {
                $status = 'cautious';
            }

            // Warning based on absences (more than 3)
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
                'has_warning' => $hasWarning,
                'total_classes' => $totalClasses,
                'class_dates' => $allClassDates
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
        'total_warnings' => $totalWarnings,
        'total_classes' => $totalClasses
    ];

    return view('lecturer.course-students', compact(
        'course',
        'lecturer',
        'studentsWithAttendance',
        'attendanceStats'
    ));
}

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

    public function getRecentAttendanceRecords(Course $course, $enrollment)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check authorization
        if ($course->lecturer_id !== $lecturer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get the enrollment record - $enrollment is now the ID value
        $enrollmentRecord = Enrollment::where('course_id', $course->id)
                               ->where('id', $enrollment) // Use $enrollment instead of $enrollmentId
                               ->with('card')
                               ->first();

        if (!$enrollmentRecord || !$enrollmentRecord->card) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // ... rest of your existing code stays the same, just replace $enrollment->card with $enrollmentRecord->card
        // Get ALL enrolled students' card IDs for THIS COURSE
        $enrolledCardIds = Enrollment::where('course_id', $course->id)
                                    ->with('card')
                                    ->get()
                                    ->pluck('card.id')
                                    ->filter();

        if ($enrolledCardIds->isEmpty()) {
            return response()->json([]);
        }

        // Get class dates where attendance was taken for THIS COURSE
        $allClassDates = Attendance::whereIn('card_id', $enrolledCardIds)
                                  ->distinct('date')
                                  ->orderBy('date', 'desc')
                                  ->pluck('date')
                                  ->take(10);

        if ($allClassDates->isEmpty()) {
            return response()->json([]);
        }

        // Get THIS student's attendance records for the class dates
        $studentAttendances = Attendance::where('card_id', $enrollmentRecord->card->id)
                                       ->whereIn('date', $allClassDates)
                                       ->get()
                                       ->keyBy('date');

        // Format the attendance data
        $formattedRecords = [];

        foreach ($allClassDates as $classDate) {
            $attendanceRecord = $studentAttendances->get($classDate);

            if ($attendanceRecord) {
                // Student was present
                $formattedRecords[] = [
                    'date' => Carbon::parse($classDate)->format('M d, Y'),
                    'time_in' => $attendanceRecord->time_in ?
                               Carbon::parse($classDate . ' ' . $attendanceRecord->time_in)->format('H:i A') : null,
                    'time_out' => $attendanceRecord->time_out ?
                                Carbon::parse($classDate . ' ' . $attendanceRecord->time_out)->format('H:i A') : null,
                    'status' => 'Present'
                ];
            } else {
                // Student was absent
                $formattedRecords[] = [
                    'date' => Carbon::parse($classDate)->format('M d, Y'),
                    'time_in' => null,
                    'time_out' => null,
                    'status' => 'Absent'
                ];
            }
        }

        return response()->json($formattedRecords);
    }

    /**
     * FIXED: Changed parameter from $enrollmentId to $enrollment
     */
    public function getStudentMedicalCertificates(Course $course, $enrollment)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check authorization
        if ($course->lecturer_id !== $lecturer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $medicalCertificates = MedicalCertificate::where('enrollment_id', $enrollment) // Use $enrollment instead of $enrollmentId
                                               ->where('course_id', $course->id)
                                               ->orderBy('absence_date', 'desc')
                                               ->get();

        $formatted = $medicalCertificates->map(function($mc) {
            return [
                'id' => $mc->id,
                'date' => $mc->absence_date->format('M d, Y'),
                'reason' => $mc->reason,
                'has_file' => !is_null($mc->file_path),
                'file_name' => $mc->original_filename,
                'file_size' => $mc->file_size_formatted,
                'file_icon' => $mc->file_icon,
                'uploaded_at' => $mc->uploaded_at->format('M d, Y H:i'),
                'file_url' => $mc->file_path ? route('lecturer.mc.download', ['course' => $mc->course_id, 'mc' => $mc->id]) : null
            ];
        });

        return response()->json($formatted);
    }


    public function markPresentWithReason(Request $request, Course $course, $enrollment)
{
    $lecturer = Auth::guard('lecturer')->user();

    // Check authorization
    if ($course->lecturer_id !== $lecturer->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // DEBUG: Log what we're receiving
    Log::info('MC Upload Debug:', [  // ✅ FIXED: Now properly imported
        'has_file' => $request->hasFile('mc_file'),
        'file_data' => $request->file('mc_file') ? [
            'original_name' => $request->file('mc_file')->getClientOriginalName(),
            'mime_type' => $request->file('mc_file')->getMimeType(),
            'size' => $request->file('mc_file')->getSize(),
            'is_valid' => $request->file('mc_file')->isValid()
        ] : null,
        'all_files' => $request->allFiles(),
        'request_data' => $request->all()
    ]);

    // Validate input
    $request->validate([
        'date' => 'required|date',
        'reason' => 'required|string|max:500',
        'mc_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
    ]);

    // Get the enrollment record
    $enrollmentRecord = Enrollment::where('course_id', $course->id)
                           ->where('id', $enrollment)
                           ->with('card')
                           ->first();

    if (!$enrollmentRecord || !$enrollmentRecord->card) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    $date = $request->input('date');
    $reason = $request->input('reason');

    // Check if student already has attendance for this date
    $existingAttendance = Attendance::where('card_id', $enrollmentRecord->card->id)
                                   ->where('date', $date)
                                   ->first();

    if ($existingAttendance) {
        return response()->json(['error' => 'Student already marked as present for this date'], 400);
    }

    // Check if MC already exists for this date
    $existingMc = MedicalCertificate::where('enrollment_id', $enrollment)
                                   ->where('absence_date', $date)
                                   ->first();

    if ($existingMc) {
        return response()->json(['error' => 'MC/Reason already uploaded for this date'], 400);
    }

    try {
        DB::beginTransaction(); // ✅ FIXED: Now properly imported

        // Handle file upload
        $filePath = null;
        $originalFilename = null;
        $fileType = null;
        $fileSize = null;

        if ($request->hasFile('mc_file') && $request->file('mc_file')->isValid()) {
            $file = $request->file('mc_file');
            $originalFilename = $file->getClientOriginalName();
            $fileType = $file->getMimeType();
            $fileSize = $file->getSize();

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'mc_' . $enrollment . '_' . date('Y-m-d', strtotime($date)) . '_' . time() . '.' . $extension;

            // DEBUG: Check if storage directory exists and is writable
            $storagePath = storage_path('app/public/medical_certificates');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                Log::info('Created storage directory: ' . $storagePath); // ✅ FIXED
            }

            // Store file in 'medical_certificates' directory
            $filePath = $file->storeAs('medical_certificates', $filename, 'public');

            // DEBUG: Verify file was stored
            $fullPath = storage_path('app/public/' . $filePath);
            Log::info('File storage result:', [ // ✅ FIXED
                'file_path' => $filePath,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size_on_disk' => file_exists($fullPath) ? filesize($fullPath) : 'not found'
            ]);
        }

        // Create MC record
        $medicalCertificate = MedicalCertificate::create([
            'enrollment_id' => $enrollment,
            'course_id' => $course->id,
            'absence_date' => $date,
            'reason' => $reason,
            'file_path' => $filePath,
            'original_filename' => $originalFilename,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'uploaded_at' => now(),
            'uploaded_by' => $lecturer->id
        ]);

        // DEBUG: Log what was saved to database
        Log::info('MC Record created:', [ // ✅ FIXED
            'id' => $medicalCertificate->id,
            'file_path' => $medicalCertificate->file_path,
            'original_filename' => $medicalCertificate->original_filename,
            'file_size' => $medicalCertificate->file_size
        ]);

        // Create attendance record automatically marking student as present
        Attendance::create([
            'card_id' => $enrollmentRecord->card->id,
            'date' => $date,
            'time_in' => '08:00:00', // Default time for MC-based attendance
            'time_out' => null,
        ]);

        DB::commit(); // ✅ FIXED

        return response()->json([
            'success' => true,
            'message' => 'MC/Reason uploaded successfully and student marked as present.',
            'student_name' => $enrollmentRecord->card->name,
            'date' => \Carbon\Carbon::parse($date)->format('M d, Y'),
            'reason' => $reason,
            'file_name' => $originalFilename,
            'mc_id' => $medicalCertificate->id,
            'debug' => [
                'file_uploaded' => !is_null($filePath),
                'file_path' => $filePath
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack(); // ✅ FIXED
        Log::error('MC Upload Error:', [ // ✅ FIXED
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => 'Failed to upload MC/Reason: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * NEW: Get class dates where student was absent
     */
    public function getAbsentDates(Course $course, $enrollment)
{
    $lecturer = Auth::guard('lecturer')->user();

    // Check authorization
    if ($course->lecturer_id !== $lecturer->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Get the enrollment record
    $enrollmentRecord = Enrollment::where('course_id', $course->id)
                           ->where('id', $enrollment) // Use $enrollment instead of $enrollmentId
                           ->with('card')
                           ->first();

    if (!$enrollmentRecord || !$enrollmentRecord->card) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    // ... rest of your existing code stays the same, just use $enrollmentRecord instead of $enrollment
    // Get all enrolled students' card IDs for this course
    $enrolledCardIds = Enrollment::where('course_id', $course->id)
                                ->with('card')
                                ->get()
                                ->pluck('card.id')
                                ->filter();

    // Get all class dates for this course
    $allClassDates = Attendance::whereIn('card_id', $enrolledCardIds)
                              ->distinct('date')
                              ->orderBy('date', 'desc')
                              ->pluck('date');

    if ($allClassDates->isEmpty()) {
        return response()->json([]);
    }

    // Get dates where this student attended
    $attendedDates = Attendance::where('card_id', $enrollmentRecord->card->id)
                              ->whereIn('date', $allClassDates)
                              ->pluck('date')
                              ->toArray();

    // Find absent dates
    $absentDates = $allClassDates->filter(function($date) use ($attendedDates) {
        return !in_array($date, $attendedDates);
    })->map(function($date) {
        return [
            'value' => $date,
            'label' => Carbon::parse($date)->format('M d, Y (l)')
        ];
    })->values();

    return response()->json($absentDates);
}



/**
 * NEW: Get student's medical certificates for the course
 */


/**
 * NEW: Download/View MC file
 */

public function downloadMedicalCertificate(Course $course, MedicalCertificate $mc)
{
    $lecturer = Auth::guard('lecturer')->user();

    // Check authorization
    if ($course->lecturer_id !== $lecturer->id || $mc->course_id !== $course->id) {
        abort(403, 'Unauthorized');
    }

    if (!$mc->file_path || !Storage::disk('public')->exists($mc->file_path)) {
        abort(404, 'File not found');
    }

    $filePath = Storage::disk('public')->path($mc->file_path);

    return response()->file($filePath, [
        'Content-Disposition' => 'inline; filename="' . $mc->original_filename . '"'
    ]);
}

/**
 * NEW: Delete MC file and record
 */
public function deleteMedicalCertificate(Course $course, MedicalCertificate $mc)
{
    $lecturer = Auth::guard('lecturer')->user();

    // Check authorization
    if ($course->lecturer_id !== $lecturer->id || $mc->course_id !== $course->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        DB::beginTransaction(); // ✅ FIXED

        // Find and remove the corresponding attendance record
        $attendanceRecord = Attendance::where('card_id', $mc->enrollment->card->id)
                                     ->where('date', $mc->absence_date)
                                     ->where('time_in', '08:00:00') // MC-based attendance
                                     ->first();

        if ($attendanceRecord) {
            $attendanceRecord->delete();
        }

        // Delete MC record (file will be deleted automatically via model boot method)
        $mc->delete();

        DB::commit(); // ✅ FIXED

        return response()->json([
            'success' => true,
            'message' => 'MC record deleted successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack(); // ✅ FIXED
        return response()->json([
            'error' => 'Failed to delete MC record: ' . $e->getMessage()
        ], 500);
    }
}

public function sendAttendanceWarning(Request $request, Course $course, $enrollment)
{
    $lecturer = Auth::guard('lecturer')->user();

    if ($course->lecturer_id !== $lecturer->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Enhanced validation
    $validated = $request->validate([
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:2000'
    ]);

    $enrollmentRecord = Enrollment::where('course_id', $course->id)
        ->where('id', $enrollment)
        ->with('card')
        ->first();

    if (!$enrollmentRecord || !$enrollmentRecord->card) {
        return response()->json(['error' => 'Student not found'], 404);
    }

    try {
        // ENHANCED DEBUGGING
        Log::info('=== EMAIL SENDING DEBUG ===', [
            'mail_driver' => config('mail.default'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'to_email' => $validated['email'],
            'student_name' => $enrollmentRecord->card->name,
            'course_code' => $course->course_code,
            'lecturer_name' => $lecturer->name ?? 'Unknown'
        ]);

        // IMPROVED: Create the email data array properly
        $emailData = [
            'student_name'   => $enrollmentRecord->card->name,
            'student_matric' => $enrollmentRecord->card->matric_id,
            'subject'        => $validated['subject'],
            'custom_message' => $validated['message'],
            'course_code'    => $course->course_code,
            'lecturer_name'  => $lecturer->name ?? 'Lecturer',
        ];

        Log::info('Email data prepared:', $emailData);

        // FIXED: Send email with proper error handling
        Mail::to($validated['email'])->send(new AttendanceWarningMail($emailData));

        // REMOVED: The Mail::failures() check since it doesn't exist in newer Laravel versions
        // The email sending is handled by the try-catch block instead

        Log::info('Email sent successfully to: ' . $validated['email']);

        return response()->json([
            'success' => true,
            'message' => 'Warning email sent successfully to ' . $validated['email'],
        ]);

    } catch (TransportExceptionInterface $e) {
        Log::error('SMTP Transport Error:', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
        ]);

        return response()->json([
            'error' => 'SMTP connection failed. Please check mail server configuration.',
            'details' => config('app.debug') ? $e->getMessage() : 'Contact administrator'
        ], 500);

    } catch (\Exception $e) {
        Log::error('General Email Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'Failed to send email: ' . $e->getMessage()
        ], 500);
    }
}
public function getAvailableDates(Course $course)
{
    $lecturer = Auth::guard('lecturer')->user();

    if ($course->lecturer_id !== $lecturer->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Get all enrolled students
    $enrolledCardIds = Enrollment::where('course_id', $course->id)
        ->with('card')
        ->get()
        ->pluck('card.id')
        ->filter();

    if ($enrolledCardIds->isEmpty()) {
        return response()->json([]);
    }

    // Get all dates with attendance records
    $dates = DB::table('attendance')
        ->whereIn('card_id', $enrolledCardIds)
        ->select('date', DB::raw('COUNT(DISTINCT card_id) as present_count'))
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get();

    $totalStudents = Enrollment::where('course_id', $course->id)->count();

    $formatted = $dates->map(function($item) use ($totalStudents) {
        return [
            'date' => $item->date,
            'formatted_date' => Carbon::parse($item->date)->format('l, M d, Y'),
            'present_count' => $item->present_count,
            'total_students' => $totalStudents,
            'attendance_rate' => $totalStudents > 0 ? round(($item->present_count / $totalStudents) * 100, 1) : 0
        ];
    });

    return response()->json($formatted);
}

/**
 * Generate PDF attendance report
 */
public function printAttendance(Request $request, Course $course)
{
    $lecturer = Auth::guard('lecturer')->user();

    if ($course->lecturer_id !== $lecturer->id) {
        abort(403, 'Unauthorized');
    }

    // Get enrolled students
    $enrolledStudents = Enrollment::where('course_id', $course->id)
        ->with('card')
        ->orderBy('id')
        ->get();

    $enrolledCardIds = $enrolledStudents->pluck('card.id')->filter();

    if ($enrolledCardIds->isEmpty()) {
        return back()->with('error', 'No students enrolled in this course.');
    }

    // Determine which dates to include
    $dates = [];

    if ($request->has('start_date') && $request->has('end_date')) {
        // Date range selection
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $dates = DB::table('attendance')  // ✅ FIXED: Changed to plural
            ->whereIn('card_id', $enrolledCardIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->distinct()
            ->pluck('date')
            ->sort()
            ->values()
            ->toArray();

    } elseif ($request->has('dates') && is_array($request->dates)) {
        // Specific dates selection
        $dates = $request->dates;
        sort($dates);
    } else {
        return back()->with('error', 'Please select dates to print.');
    }

    if (empty($dates)) {
        return back()->with('error', 'No attendance records found for selected dates.');
    }

    // ✅ FIXED: Get attendance records as a flat collection with date as key
    $attendanceRecords = Attendance::whereIn('card_id', $enrolledCardIds)
        ->whereIn('date', $dates)
        ->get()
        ->map(function($record) {
            return [
                'card_id' => $record->card_id,
                'date' => $record->date
            ];
        });

    // ✅ FIXED: Create a lookup set for faster checking
    $attendanceLookup = $attendanceRecords->map(function($record) {
        return $record['card_id'] . '|' . $record['date'];
    })->flip(); // Creates array with keys like "1|2025-11-11"

    // Build attendance data
    $attendanceData = [];

    foreach ($enrolledStudents as $index => $enrollment) {
        if (!$enrollment->card) continue;

        $studentData = [
            'no' => $index + 1,
            'name' => $enrollment->card->name,
            'matric_id' => $enrollment->card->matric_id,
            'card_uid' => $enrollment->card->uid,
            'attendance' => []
        ];

        $presentCount = 0;

        foreach ($dates as $date) {
            // ✅ FIXED: Use lookup key to check attendance
            $lookupKey = $enrollment->card->id . '|' . $date;
            $isPresent = isset($attendanceLookup[$lookupKey]);

            $studentData['attendance'][$date] = $isPresent;

            if ($isPresent) {
                $presentCount++;
            }
        }

        $studentData['present_count'] = $presentCount;
        $studentData['absent_count'] = count($dates) - $presentCount;
        $studentData['attendance_percentage'] = count($dates) > 0
            ? round(($presentCount / count($dates)) * 100, 1)
            : 0;

        // Filter if only showing absent students
        if ($request->has('absent_only') && $studentData['absent_count'] == 0) {
            continue;
        }

        $attendanceData[] = $studentData;
    }

    // Sort by attendance percentage (lowest first)
    usort($attendanceData, function($a, $b) {
        return $a['attendance_percentage'] <=> $b['attendance_percentage'];
    });

    // Calculate summary statistics
    $summary = [
        'total_students' => count($attendanceData),
        'total_classes' => count($dates),
        'average_attendance' => count($attendanceData) > 0
            ? round(array_sum(array_column($attendanceData, 'attendance_percentage')) / count($attendanceData), 1)
            : 0,
        'date_range' => count($dates) > 0
            ? Carbon::parse($dates[0])->format('M d, Y') . ' - ' . Carbon::parse(end($dates))->format('M d, Y')
            : 'N/A'
    ];

    // Prepare data for PDF
    $data = [
        'course' => $course,
        'lecturer' => $lecturer,
        'dates' => array_map(function($date) {
            return Carbon::parse($date)->format('M d');
        }, $dates),
        'full_dates' => $dates, // ✅ Keep full dates for matching
        'students' => $attendanceData,
        'summary' => $summary,
        'include_summary' => $request->has('include_summary'),
        'generated_at' => Carbon::now()->format('M d, Y H:i A')
    ];

    // Generate PDF
    $pdf = PDFService::loadView('lecturer.pdf.attendance-report', $data);
    $pdf->setPaper('a4', 'landscape');

    $filename = 'Attendance_' . $course->course_code . '_' . date('Ymd_His') . '.pdf';

    return $pdf->download($filename);
}
}
