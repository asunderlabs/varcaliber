<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return auth()->user()->is_admin && ! session('activeOrganization') ? 
            Inertia::render('Admin/Dashboard', DashboardService::adminDashboard($request)) : 
            Inertia::render('Dashboard', DashboardService::userDashboard($request));
    }
}
