<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecturer;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function create()
    {
        $lecturers = Lecturer::all();
        return view('courses', compact('lecturers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_code' => ['required', 'string', 'max:255', 'unique:courses'],
            'title' => ['required', 'string', 'max:255'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:6'],
            'section' => ['required', 'string', 'max:255'],
            'lecturer_id' => ['required', 'exists:lecturers,id'],
        ]);

        Course::create([
            'course_code' => $request->course_code,
            'title' => $request->title,
            'credit_hours' => $request->credit_hours,
            'section' => $request->section,
            'lecturer_id' => $request->lecturer_id,
        ]);

        return redirect()->route('course.create')->with('success', 'Course registered successfully!');
    }

    public function index()
    {
        $courses = Course::with('lecturer')->get();
        return view('course.index', compact('courses'));
    }
}
