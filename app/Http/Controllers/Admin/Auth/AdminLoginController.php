<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function create(): View
    {
         return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $remember = $request->boolean('remember');
        $throttleKey = 'admin-login:' . strtolower(trim($credentials['login'])) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'login' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
            ]);
        }

        $field = filter_var(
            $credentials['login'],
            FILTER_VALIDATE_EMAIL
        )
            ? 'email'
            : 'mobile';

        if (
            !Auth::attempt([
                $field => $credentials['login'],
                'password' => $credentials['password'],
                'is_active' => true,
            ], $remember)
        ) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors([
                    'login' => 'Invalid login credentials.',
                ])
                ->onlyInput('login');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        if ($request->user()->hasRole('customer')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['login' => 'Customer accounts must use the customer login.'])->onlyInput('login');
        }

        $request->user()->forceFill(['last_login_at' => now()])->save();
        AuditLog::record('auth.admin_login', $request->user(), [], [], 'Admin login successful.');

        return redirect()
            ->intended(
                route('admin.dashboard')
            );
    }

    public function destroy(
        Request $request
    ): RedirectResponse {

        $user = Auth::user();
        if ($user) {
            AuditLog::record('auth.logout', $user, [], [], 'Admin logout.');
        }
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route(
            'admin.login'
        );
    }
}