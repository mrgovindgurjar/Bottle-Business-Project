<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SecurityRoleController extends Controller
{
    private function authorizePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()?->hasPermission($permission), 403);
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request, 'roles.view');
        $roles = Role::query()->withCount(['users', 'permissions'])->orderBy('name')->get();
        return view('admin.security.roles.index', compact('roles'));
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request, 'roles.create');
        $permissions = Permission::query()->orderBy('module')->orderBy('action')->get();
        return view('admin.security.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission($request, 'roles.create');
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);
            $role->permissions()->sync($data['permissions'] ?? []);
            AuditLog::record('role.created', $role, [], ['name'=>$role->name,'slug'=>$role->slug]);
        });

        return redirect()->route('admin.security.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Request $request, Role $role)
    {
        $this->authorizePermission($request, 'roles.edit');
        $permissions = Permission::query()->orderBy('module')->orderBy('action')->get();
        $role->load('permissions:id,slug');
        return view('admin.security.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizePermission($request, 'roles.edit');
        $data = $this->validated($request, $role);

        if ($role->slug === 'super-admin' && ($data['slug'] !== 'super-admin' || !($data['is_active'] ?? false))) {
            return back()->withErrors(['role' => 'The Super Admin role must remain active and keep its reserved slug.'])->withInput();
        }

        DB::transaction(function () use ($data, $role) {
            $role->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);
            $role->permissions()->sync($data['permissions'] ?? []);
            AuditLog::record('role.updated', $role, [], ['name'=>$role->name,'slug'=>$role->slug,'is_active'=>$role->is_active]);
        });

        return redirect()->route('admin.security.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role)
    {
        $this->authorizePermission($request, 'roles.delete');

        if ($role->slug === 'super-admin') {
            return back()->withErrors(['role' => 'The Super Admin role cannot be deleted.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role is assigned to users. Reassign them before deleting the role.']);
        }

        $role->delete();
        AuditLog::record('role.deleted', $role, ['name'=>$role->name,'slug'=>$role->slug], []);
        return back()->with('success', 'Role deleted successfully.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', Rule::unique('roles', 'slug')->ignore($role?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
