<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
