<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        if (!auth()->check()) {
            return redirect()->route(
                'admin.login'
            );
        }

        if (!auth()->user()->is_active) {
            auth()->logout();

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'login' => 'Your account is inactive.',
                ]);
        }

        if (!auth()->user()->roles()->where('slug', '!=', 'customer')->exists()) {
            return redirect()->route('customer.main');
        }

        return $next($request);
    }
}