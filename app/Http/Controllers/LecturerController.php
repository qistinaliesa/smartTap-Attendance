<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Hash;

class LecturerController extends Controller
{
    public function create()
    {
        $lecturers = \App\Models\Lecturer::all(); // Import if needed
        return view('admin.lecturer', compact('lecturers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:lecturers',
            'staff_id' => 'nullable',
            'kulliyyah' => 'required',
            'department' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        Lecturer::create([
            'name' => $request->name,
            'email' => $request->email,
            'staff_id' => $request->staff_id,
            'kulliyyah' => $request->kulliyyah,
            'department' => $request->department,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.lecturer.create')->with('success', 'Lecturer registered successfully!');
    }
}

