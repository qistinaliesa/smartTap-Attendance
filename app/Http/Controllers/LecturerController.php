<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LecturerController extends Controller
{
    /**
     * Show the lecturer registration form
     */
    public function showRegistrationForm()
{
    $lecturers = \App\Models\Lecturer::orderBy('created_at', 'desc')->get();
    return view('admin.lecturer', compact('lecturers'));
}



    /**
     * Handle lecturer registration
     */
    public function register(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:lecturers',
            'staff_id' => 'nullable|string|max:255|unique:lecturers',
            'kulliyyah' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create new lecturer record
            $lecturer = Lecturer::create([
                'name' => $request->name,
                'email' => $request->email,
                'staff_id' => $request->staff_id,
                'kulliyyah' => $request->kulliyyah,
                'department' => $request->department,
                'password' => Hash::make($request->password), // Hash the password
            ]);

            // Redirect with success message
            return redirect()->back()
    ->with('success', 'Lecturer registered successfully!')
    ->with('lecturer', $lecturer);

        } catch (\Exception $e) {
            // Handle any database errors
            return redirect()->back()
                ->with('error', 'Failed to register lecturer. Please try again.')
                ->withInput();
        }
    }
}
