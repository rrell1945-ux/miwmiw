<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $accounts = \App\Models\User::query()
            ->orderBy('role')
            ->get()
            ->map(fn ($account) => [
                'name' => $account->name,
                'email' => $account->email,
                'role' => $account->role,
                'initials' => mb_strtoupper(mb_substr(trim($account->name), 0, 1)),
            ])
            ->values();

        return view('auth.login', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        Auth::login($user);

        $request->session()->regenerate();

        app(ActivityLogService::class)->log($user, 'login', 'Masuk ke Mimiw');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            app(ActivityLogService::class)->log($user, 'logout', 'Keluar dari Mimiw');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
