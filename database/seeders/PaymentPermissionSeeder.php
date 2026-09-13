<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PaymentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'View Payments',
                'payments.view',
                'payments',
                'view',
            ],
            [
                'View All Payments',
                'payments.view_all',
                'payments',
                'view_all',
            ],
            [
                'Create Payments',
                'payments.create',
                'payments',
                'create',
            ],
            [
                'Edit Payments',
                'payments.edit',
                'payments',
                'edit',
            ],
            [
                'Delete Payments',
                'payments.delete',
                'payments',
                'delete',
            ],
            [
                'Allocate Payments',
                'payments.allocate',
                'payments',
                'allocate',
            ],
            [
                'Refund Payments',
                'payments.refund',
                'payments',
                'refund',
            ],
            [
                'Approve Payments',
                'payments.approve',
                'payments',
                'approve',
            ],
            [
                'Export Payments',
                'payments.export',
                'payments',
                'export',
            ],
            [
                'Payment Receipts',
                'payments.receipt',
                'payments',
                'receipt',
            ],
        ];

        // foreach ($permissions as $permission) {
        //     Permission::updateOrCreate(
        //         ['slug' => $permission['slug']],
        //         $permission
        //     );
        // }

          foreach($permissions as [$name,$slug,$module,$action]) DB::table('permissions')->updateOrInsert(['slug'=>$slug],['name'=>$name,'module'=>$module,'action'=>$action,'updated_at'=>now(),'created_at'=>now()]);
  $ids=DB::table('permissions')->whereIn('slug',array_column($permissions,0))->pluck('id');
  foreach(['super-admin','manager','production'] as $roleSlug){$role=DB::table('roles')->where('slug',$roleSlug)->first(); if($role) foreach($ids as $id) DB::table('role_permissions')->updateOrInsert(['role_id'=>$role->id,'permission_id'=>$id],['created_at'=>now(),'updated_at'=>now()]);}
    }
}