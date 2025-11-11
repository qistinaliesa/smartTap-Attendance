<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LecturerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $lecturers = Lecturer::all();
    return view('admin.lecturer', compact('lecturers'));
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lecturers = Lecturer::all();
    return view('admin.lecturer', compact('lecturers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:lecturers',
            'staff_id' => 'nullable|string|max:255|unique:lecturers',
            'kulliyyah' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $lecturer = Lecturer::create([
                'name' => $request->name,
                'email' => $request->email,
                'staff_id' => $request->staff_id,
                'kulliyyah' => $request->kulliyyah,
                'department' => $request->department,
                'password' => Hash::make($request->password),
            ]);

            return redirect()->back()
                ->with('success', 'Lecturer registered successfully!')
                ->with('lecturer', $lecturer);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to register lecturer. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lecturer $lecturer)
    {
        return view('admin.lecturer-show', compact('lecturer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lecturer $lecturer)
    {
        return response()->json($lecturer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lecturer $lecturer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:lecturers,email,' . $lecturer->id,
            'staff_id' => 'nullable|string|max:255|unique:lecturers,staff_id,' . $lecturer->id,
            'kulliyyah' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'staff_id' => $request->staff_id,
                'kulliyyah' => $request->kulliyyah,
                'department' => $request->department,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $lecturer->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Lecturer updated successfully!',
                'lecturer' => $lecturer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lecturer. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lecturer $lecturer)
    {
        try {
            $lecturer->delete();
            return response()->json([
                'success' => true,
                'message' => 'Lecturer deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lecturer. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle lecturer registration (for the registration form)
     */
    public function register(Request $request)
    {
         $lecturers = Lecturer::all();
        return $this->store($request);
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        $lecturers = Lecturer::all();
        return view('admin.lecturer', compact('lecturers'));
    }
    public function showChangePasswordForm()
{
    return view('lecturer.change-password');
}

/**
 * Handle change password request
 */
/**
 * Handle change password request
 */
public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
    ]);

    $lecturer = Auth::guard('lecturer')->user();

    // Check if current password is correct
    if (!Hash::check($request->current_password, $lecturer->password)) {
        return back()->withErrors([
            'current_password' => 'Current password is incorrect.'
        ]);
    }

    // Update password
    try {
        $lecturer->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Log out the lecturer after password change
        Auth::guard('lecturer')->logout();

        // Invalidate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login with success message
        return redirect('/login')->with('success', 'Password changed successfully! Please log in with your new password.');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to change password. Please try again.');
    }
}
}
