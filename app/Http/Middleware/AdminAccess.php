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

        $user = auth()->user();

        if ($user->hasRole('customer')) {
            return redirect()->route('customer.main');
        }

        if (!$user->roles()->where('is_active', true)->where('slug', '!=', 'customer')->exists()) {
            abort(403, 'No administrative role is assigned to this account.');
        }

        return $next($request);
    }
}