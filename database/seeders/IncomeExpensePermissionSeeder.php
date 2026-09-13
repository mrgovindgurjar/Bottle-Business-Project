<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncomeExpensePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name'   => 'View Income',
                'slug'   => 'income.view',
                'module' => 'income',
                'action' => 'view',
            ],
            [
                'name'   => 'Create Income',
                'slug'   => 'income.create',
                'module' => 'income',
                'action' => 'create',
            ],
            [
                'name'   => 'Edit Income',
                'slug'   => 'income.edit',
                'module' => 'income',
                'action' => 'edit',
            ],
            [
                'name'   => 'Cancel Income',
                'slug'   => 'income.cancel',
                'module' => 'income',
                'action' => 'cancel',
            ],

            [
                'name'   => 'View Expenses',
                'slug'   => 'expenses.view',
                'module' => 'expenses',
                'action' => 'view',
            ],
            [
                'name'   => 'Create Expense',
                'slug'   => 'expenses.create',
                'module' => 'expenses',
                'action' => 'create',
            ],
            [
                'name'   => 'Edit Expense',
                'slug'   => 'expenses.edit',
                'module' => 'expenses',
                'action' => 'edit',
            ],
            [
                'name'   => 'Cancel Expense',
                'slug'   => 'expenses.cancel',
                'module' => 'expenses',
                'action' => 'cancel',
            ],

            [
                'name'   => 'Manage Finance Categories',
                'slug'   => 'finance.categories',
                'module' => 'finance',
                'action' => 'categories',
            ],
            [
                'name'   => 'Export Finance',
                'slug'   => 'finance.export',
                'module' => 'finance',
                'action' => 'export',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                [
                    'slug' => $permission['slug'],
                ],
                [
                    'name'       => $permission['name'],
                    'module'     => $permission['module'],
                    'action'     => $permission['action'],
                    'updated_at' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assign permissions to roles
        |--------------------------------------------------------------------------
        */

        $permissionIds = DB::table('permissions')
            ->whereIn('slug', array_column($permissions, 'slug'))
            ->pluck('id');

        /*
         * Finance permissions should normally be available to:
         * Super Admin
         * Manager
         * Accountant
         */

        $roleSlugs = [
            'super-admin',
            'manager',
            'accountant',
        ];

        foreach ($roleSlugs as $roleSlug) {

            $role = DB::table('roles')
                ->where('slug', $roleSlug)
                ->first();

            if (!$role) {
                continue;
            }

            foreach ($permissionIds as $permissionId) {

                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_id'       => $role->id,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}