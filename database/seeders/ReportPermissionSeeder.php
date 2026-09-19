<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name'=>'View Reports','slug'=>'reports.view','module'=>'reports','action'=>'view'],
            ['name'=>'Export Reports','slug'=>'reports.export','module'=>'reports','action'=>'export'],
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['slug'=>$p['slug']],
                ['name'=>$p['name'],'module'=>$p['module'],'action'=>$p['action'],'updated_at'=>now()]
            );
        }

        $ids = DB::table('permissions')
            ->whereIn('slug', array_column($permissions,'slug'))
            ->pluck('id');

        foreach (['super-admin','manager','accountant'] as $roleSlug) {
            $role = DB::table('roles')->where('slug',$roleSlug)->first();
            if (!$role) continue;
            foreach ($ids as $id) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id'=>$role->id,'permission_id'=>$id],
                    ['updated_at'=>now(),'created_at'=>now()]
                );
            }
        }
    }
}
