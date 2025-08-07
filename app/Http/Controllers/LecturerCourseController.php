<?php

namespace App\Http\Controllers;

use App\Models\Course;
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
     * Show details of a specific course assigned to the lecturer
     */
    public function show(Course $course)
    {
        $lecturer = Auth::guard('lecturer')->user();

        // Check if this course belongs to the authenticated lecturer
        if ($course->lecturer_id !== $lecturer->id) {
            abort(403, 'You are not authorized to view this course.');
        }

        return view('lecturer.course-detail', compact('course', 'lecturer'));
    }
}
