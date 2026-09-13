<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffAttendancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name'=>'View Staff','slug'=>'staff.view','module'=>'staff','action'=>'view'],
            ['name'=>'Create Staff','slug'=>'staff.create','module'=>'staff','action'=>'create'],
            ['name'=>'Edit Staff','slug'=>'staff.edit','module'=>'staff','action'=>'edit'],
            ['name'=>'Delete Staff','slug'=>'staff.delete','module'=>'staff','action'=>'delete'],
            ['name'=>'View Attendance','slug'=>'attendance.view','module'=>'attendance','action'=>'view'],
            ['name'=>'Mark Attendance','slug'=>'attendance.mark','module'=>'attendance','action'=>'mark'],
            ['name'=>'Edit Attendance','slug'=>'attendance.edit','module'=>'attendance','action'=>'edit'],
            ['name'=>'Delete Attendance','slug'=>'attendance.delete','module'=>'attendance','action'=>'delete'],
            ['name'=>'Export Attendance','slug'=>'attendance.export','module'=>'attendance','action'=>'export'],
        ];
        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['slug'=>$p['slug']],
                ['name'=>$p['name'],'module'=>$p['module'],'action'=>$p['action'],'updated_at'=>now()]
            );
        }
        $ids = DB::table('permissions')->whereIn('slug',array_column($permissions,'slug'))->pluck('id');
        foreach (['super-admin','manager','hr'] as $roleSlug) {
            $role = DB::table('roles')->where('slug',$roleSlug)->first();
            if (!$role) continue;
            foreach ($ids as $id) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id'=>$role->id,'permission_id'=>$id],
                    ['updated_at'=>now()]
                );
            }
        }
    }
}
