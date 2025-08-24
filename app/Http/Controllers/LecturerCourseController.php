<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Show details of a specific course and enrolled students
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

        // Return the course-students view (not course-detail)
        return view('lecturer.course-students', compact('course', 'lecturer', 'enrolledStudents'));
    }
}
