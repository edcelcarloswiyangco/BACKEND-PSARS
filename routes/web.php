<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Developer\AuthController as DeveloperAuthController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth('admin')->check()) {
        return auth('admin')->user()->role === 'developer'
            ? redirect()->route('developer.dashboard')
            : redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

// Admin Routes
Route::get('/admin/login', [AuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login.store');
Route::post('/admin/logout', [AuthController::class, 'destroy'])->name('admin.logout');

Route::middleware('admin.auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users/{user}/details', [DashboardController::class, 'show'])->name('admin.users.show');
    Route::patch('/admin/reports/{report}/status', [DashboardController::class, 'updateReportStatus'])->name('admin.reports.status');
});

// Developer Routes
Route::get('/developer/login', [DeveloperAuthController::class, 'create'])->name('developer.login');
Route::post('/developer/login', [DeveloperAuthController::class, 'store'])->name('developer.login.store');
Route::post('/developer/logout', [DeveloperAuthController::class, 'destroy'])->name('developer.logout');

Route::middleware('developer.only')->group(function () {
    Route::get('/developer/dashboard', [DeveloperDashboardController::class, 'index'])->name('developer.dashboard');
    Route::post('/developer/admin', [DeveloperDashboardController::class, 'storeAdmin'])->name('developer.admin.store');
    Route::delete('/developer/admin/{admin}', [DeveloperDashboardController::class, 'deleteAdmin'])->name('developer.admin.destroy');
});
