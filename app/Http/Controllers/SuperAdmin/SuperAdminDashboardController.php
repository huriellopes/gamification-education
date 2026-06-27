<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Inertia\Inertia;

class SuperAdminDashboardController extends Controller
{
    /**
     * Exibe a dashboard do Super Admin.
     */
    public function index()
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'institutions' => Institution::withCount(['users', 'subjects'])->get(),
            'admins' => User::where('role', 'admin')->with('institution')->get(),
        ]);
    }
}
