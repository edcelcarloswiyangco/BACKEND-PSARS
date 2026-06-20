<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\HttpLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $admins = Admin::where('role', 'admin')->get();
        // Fetch the latest 50 HTTP requests
        $logs = HttpLog::latest()->take(50)->get();

        return view('developer.dashboard', [
            'admins' => $admins,
            'logs' => $logs,
        ]);
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        Admin::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return back()->with('success', 'Admin account created successfully.');
    }

    public function deleteAdmin(Admin $admin)
    {
        if ($admin->role !== 'admin') {
            return back()->withErrors(['message' => 'Can only delete admin accounts.']);
        }

        $admin->delete();

        return back()->with('success', 'Admin account deleted successfully.');
    }
}
