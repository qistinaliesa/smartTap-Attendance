<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class LecturerController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        // Get all lecturers to display in table
        $lecturers = User::where('utype', 'user')->orderBy('created_at', 'desc')->get();
        return view('admin.lecturer', compact('lecturers'));
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'staff_id' => 'required|string|max:20|unique:users',
            'kulliyyah' => 'required|string|in:KICT,ECONS,AIKOL',
            'department' => 'required|string|in:BIT,BCS,AIKOL',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'staff_id' => $request->staff_id,
            'kulliyyah' => $request->kulliyyah,
            'department' => $request->department,
            'password' => Hash::make($request->password),
            'utype' => 'user', // Default to lecturer
        ]);

        // Redirect back to registration form with success message and updated table
        return redirect()->route('lecturer.register.form')->with('success', 'Lecturer registered successfully!');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect to dashboard after successful login
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        return view('dashboard');
    }
}
