<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function dashboard()
    {
        return view('dashboard.index');
    }
}
