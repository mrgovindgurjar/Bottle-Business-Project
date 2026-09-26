<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\AuditLog;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function profile(Request $request): View
    {
        $user = $request->user()->load('roles');
        return view('admin.account.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'mobile' => ['required', 'string', 'max:30', 'unique:users,mobile,'.$user->id],
        ]);

        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }

        $user->update($data);
        AuditLog::record('account.profile_updated', $user, [], ['name'=>$user->name,'email'=>$user->email,'mobile'=>$user->mobile]);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);
        AuditLog::record('account.password_changed', $request->user(), [], [], 'User changed their password.');
        return back()->with('success', 'Password changed successfully.');
    }
}
