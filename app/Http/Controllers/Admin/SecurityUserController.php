<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SecurityUserController extends Controller
{
    private function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->hasPermission($permission), 403);
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request, 'users.view');

        $users = User::query()
            ->whereDoesntHave('roles', fn ($role) => $role->where('slug', 'customer'))
            ->with(['roles:id,name,slug', 'customer:id,user_id,business_name'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->string('q')->toString());
                $q->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%")
                        ->orWhereHas('roles', fn ($r) => $r->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.security.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request, 'users.create');
        $roles = Role::where('is_active', true)->where('slug', '!=', 'customer')->orderBy('name')->get(['id', 'name', 'slug']);
        return view('admin.security.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission($request, 'users.create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:30', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where(fn ($q) => $q->where('is_active', true)->where('slug', '!=', 'customer'))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $targetRole = Role::findOrFail((int) $data['role_id']);
        $this->guardRoleAssignment($request->user(), null, $targetRole);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => strtolower($data['email']),
                'mobile' => $data['mobile'],
                'password' => Hash::make($data['password']),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);
            $user->roles()->sync([$data['role_id']]);
            AuditLog::record('user.created', $user, [], ['name'=>$user->name,'email'=>$user->email,'role_id'=>$data['role_id']]);
        });

        return redirect()->route('admin.security.users.index')->with('success', 'User created successfully.');
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizePermission($request, 'users.edit');
        $this->guardManagedUser($user);
        $roles = Role::where('is_active', true)->where('slug', '!=', 'customer')->orderBy('name')->get(['id', 'name', 'slug']);
        $user->load('roles:id,name,slug', 'roles.permissions:id,name,slug,module,action');
        return view('admin.security.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizePermission($request, 'users.edit');
        $this->guardManagedUser($user);

        if ($request->user()->is($user) && !$request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('users', 'mobile')->ignore($user->id)],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where(fn ($q) => $q->where('is_active', true)->where('slug', '!=', 'customer'))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $targetRole = Role::findOrFail((int) $data['role_id']);
        $this->guardRoleAssignment($request->user(), $user, $targetRole);

        DB::transaction(function () use ($data, $user) {
            $user->update([
                'name' => trim($data['name']),
                'email' => strtolower($data['email']),
                'mobile' => $data['mobile'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);
            $user->roles()->sync([$data['role_id']]);
            AuditLog::record('user.updated', $user, [], ['name'=>$user->name,'email'=>$user->email,'role_id'=>$data['role_id'],'is_active'=>$user->is_active]);
        });

        return redirect()->route('admin.security.users.index')->with('success', 'User updated successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorizePermission($request, 'users.reset-password');
        $this->guardManagedUser($user);

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);
        AuditLog::record('user.password_reset', $user, [], [], 'Administrator reset the user password.');

        return back()->with('success', 'Password reset successfully.');
    }

    private function guardManagedUser(User $user): void
    {
        abort_if($user->hasRole('customer'), 403, 'Customer accounts are managed from the Customer module.');
    }

    private function guardRoleAssignment(User $actor, ?User $targetUser, Role $targetRole): void
    {
        if ($targetRole->slug === 'customer') {
            abort(403, 'Customer role cannot be assigned from admin user management.');
        }

        if ($targetRole->slug === 'super-admin' && !$actor->hasRole('super-admin')) {
            abort(403, 'Only Super Admin can assign the Super Admin role.');
        }

        if ($targetUser?->hasRole('super-admin') && $targetRole->slug !== 'super-admin') {
            abort(403, 'The Super Admin role cannot be removed from a Super Admin account.');
        }
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizePermission($request, 'users.delete');
        $this->guardManagedUser($user);

        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');

        if ($user->hasRole('super-admin')) {
            abort(403, 'Super Admin accounts cannot be deactivated from user management.');
        }

        $user->update(['is_active' => false]);
        AuditLog::record('user.deactivated', $user, ['is_active'=>true], ['is_active'=>false]);
        return back()->with('success', 'User has been deactivated.');
    }
}
