<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return back()
                ->withErrors([
                    'login' => 'Invalid login credentials.',
                ])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(
                route('admin.dashboard')
            );
    }

    public function destroy(
        Request $request
    ): RedirectResponse {

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route(
            'admin.login'
        );
    }
}