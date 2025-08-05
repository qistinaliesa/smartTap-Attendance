<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;



class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.home');
    }
    public function home()
{
    // You can return a view or just a message for now
    return view('admin.home'); // Make sure this view exists in resources/views/admin/home.blade.php
}

public function storeLecturer(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'utype' => 'user', // default to 'user'
    ]);

    return redirect()->back()->with('success', 'Lecturer registered successfully!');
}public function showLecturerForm()
{
    $lecturers = User::where('utype', 'user')->get();
    return view('admin.lecturer', compact('lecturers'));
}

}
