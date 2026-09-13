<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BatchPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['batches.view', 'Batches', 'view'], ['batches.create', 'Batches', 'create'], ['batches.edit', 'Batches', 'edit'],
            ['batches.delete', 'Batches', 'delete'], ['batches.release', 'Batches', 'release'], ['batches.block', 'Batches', 'block'],
            ['batches.allocate', 'Batches', 'allocate'], ['batches.export', 'Batches', 'export'],
        ];
        foreach ($items as [$slug, $module, $action]) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $slug],
                ['name' => ucwords(str_replace(['.', '_'], ' ', $slug)), 'module' => $module, 'action' => $action, 'updated_at' => now(), 'created_at' => now()]
            );
        }
        $ids = DB::table('permissions')->whereIn('slug', array_column($items, 0))->pluck('id');
        foreach (['super-admin', 'production'] as $roleSlug) {
            $role = DB::table('roles')->where('slug', $roleSlug)->first();
            if ($role) foreach ($ids as $permissionId) DB::table('role_permissions')->updateOrInsert(['role_id' => $role->id, 'permission_id' => $permissionId], ['created_at' => now(), 'updated_at' => now()]);
        }
        $manager = DB::table('roles')->where('slug', 'manager')->first();
        if ($manager) foreach (['batches.view','batches.create','batches.edit','batches.release','batches.block','batches.allocate','batches.export'] as $slug) {
            $permission = DB::table('permissions')->where('slug', $slug)->first();
            if ($permission) DB::table('role_permissions')->updateOrInsert(['role_id' => $manager->id, 'permission_id' => $permission->id], ['created_at' => now(), 'updated_at' => now()]);
        }
    }
}
