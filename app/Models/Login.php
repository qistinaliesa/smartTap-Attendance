<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\LoginController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // Your Blade login view
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Redirect based on user type (admin/user)
            if ($user->utype === 'admin') {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/user/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput($request->only('email', 'remember'));
    }

    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('utype')->default('user');
    });
}

}
