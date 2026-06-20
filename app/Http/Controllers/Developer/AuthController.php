<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role === 'developer') {
            return redirect()->route('developer.dashboard');
        }

        return view('developer.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Invalid developer credentials.',
                ]);
        }

        $admin = Auth::guard('admin')->user();

        if ($admin->role !== 'developer') {
            Auth::guard('admin')->logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'This account is not a developer account.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->route('developer.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('developer.login');
    }
}
