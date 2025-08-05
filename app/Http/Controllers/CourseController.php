<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of courses with registration form
     */
    public function index()
    {
        $courses = Course::with('lecturer')->get();
        $lecturers = Lecturer::all();
        return view('admin.courses', compact('courses', 'lecturers'));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $lecturers = Lecturer::all();
        $courses = Course::with('lecturer')->get();
        return view('admin.courses', compact('lecturers', 'courses'));
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:255|unique:courses',
            'title' => 'required|string|max:255',
            'credit_hours' => 'required|integer|min:1|max:6',
            'section' => 'required|string|max:255',
            'lecturer_id' => 'required|exists:lecturers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $course = Course::create([
                'course_code' => $request->course_code,
                'title' => $request->title,
                'credit_hours' => $request->credit_hours,
                'section' => $request->section,
                'lecturer_id' => $request->lecturer_id,
            ]);

            return redirect()->back()
                ->with('success', 'Course registered successfully!')
                ->with('course', $course);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to register course. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        return view('admin.course-show', compact('course'));
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        $course->load('lecturer');
        return response()->json($course);
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:255|unique:courses,course_code,' . $course->id,
            'title' => 'required|string|max:255',
            'credit_hours' => 'required|integer|min:1|max:6',
            'section' => 'required|string|max:255',
            'lecturer_id' => 'required|exists:lecturers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $course->update([
                'course_code' => $request->course_code,
                'title' => $request->title,
                'credit_hours' => $request->credit_hours,
                'section' => $request->section,
                'lecturer_id' => $request->lecturer_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully!',
                'course' => $course->load('lecturer')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update course. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        try {
            $course->delete();
            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete course. Please try again.'
            ], 500);
        }
    }

    /**
     * Show course registration form
     */
    public function showRegistrationForm()
    {
        $lecturers = Lecturer::all();
        $courses = Course::with('lecturer')->get();
        return view('admin.courses', compact('lecturers', 'courses'));
    }
}
