<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;
        $remember = $request->boolean('remember');

        // IMPORTANT: Clear all existing authentication sessions first
        Auth::logout();
        Auth::guard('lecturer')->logout();
        $request->session()->flush();

        // First, try to authenticate as a regular user
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Redirect based on user type
            switch ($user->utype) {
                case 'admin':
                    return redirect()->intended('/admin/home');
                case 'user':
                default:
                    return redirect()->intended('/users/home');
            }
        }

        // If user authentication fails, try lecturer authentication
        $lecturer = Lecturer::where('email', $email)->first();

        if ($lecturer && Hash::check($password, $lecturer->password)) {
            // Manually log in the lecturer using the lecturer guard
            Auth::guard('lecturer')->login($lecturer, $remember);
            $request->session()->regenerate();

            return redirect()->intended('/lecturer/courses');
        }

        // If both fail, return error
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        // Check which guard is currently authenticated and logout accordingly
        if (Auth::guard('lecturer')->check()) {
            Auth::guard('lecturer')->logout();
        }

        if (Auth::check()) {
            Auth::logout();
        }

        // Clear the session completely
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Optional: Clear all session data
        $request->session()->flush();

        return redirect()->route('login');
    }

    /**
     * Separate logout method for lecturers (optional but recommended)
     */
    public function lecturerLogout(Request $request)
    {
        Auth::guard('lecturer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();

        return redirect()->route('login');
    }
}
