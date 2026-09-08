<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Permission Matrix
        |--------------------------------------------------------------------------
        */

        $modules = [

            'dashboard' => [
                'view',
                'export',
            ],

            'leads' => [
                'view',
                'create',
                'edit',
                'delete',
                'assign',
                'followup',
                'convert',
                'export',
            ],

            'customers' => [
                'view',
                'create',
                'edit',
                'delete',
                'block',
                'export',
            ],

            'products' => [
                'view',
                'create',
                'edit',
                'delete',
                'export',
            ],

            'pricing' => [
                'view',
                'create',
                'edit',
                'delete',
                'export',
            ],

            'designs' => [
                'view',
                'create',
                'edit',
                'approve',
                'reject',
                'comment',
                'export',
            ],

            'quotations' => [
                'view',
                'create',
                'edit',
                'delete',
                'send',
                'approve',
                'reject',
                'convert',
                'export',
            ],

            'orders' => [
                'view',
                'create',
                'edit',
                'delete',
                'approve',
                'reject',
                'cancel',
                'export',
            ],

            'recurring-plans' => [
                'view',
                'create',
                'edit',
                'delete',
                'pause',
                'resume',
                'cancel',
                'export',
            ],

            'daily-orders' => [
                'view',
                'create',
                'edit',
                'confirm',
                'cancel',
                'export',
            ],

            'deliveries' => [
                'view',
                'create',
                'edit',
                'assign',
                'dispatch',
                'complete',
                'cancel',
                'export',
            ],

            'batches' => [
                'view',
                'create',
                'edit',
                'close',
                'trace',
                'export',
            ],

            'inventory' => [
                'view',
                'create',
                'edit',
                'adjust',
                'transfer',
                'export',
            ],

            'suppliers' => [
                'view',
                'create',
                'edit',
                'delete',
                'export',
            ],

            'purchases' => [
                'view',
                'create',
                'edit',
                'delete',
                'approve',
                'receive',
                'cancel',
                'export',
            ],

            'payments' => [
                'view',
                'create',
                'edit',
                'delete',
                'verify',
                'refund',
                'export',
            ],

            'invoices' => [
                'view',
                'create',
                'edit',
                'delete',
                'send',
                'mark-paid',
                'cancel',
                'download',
                'export',
            ],

            'income-expenses' => [
                'view',
                'create',
                'edit',
                'delete',
                'approve',
                'export',
            ],

            'staff' => [
                'view',
                'create',
                'edit',
                'delete',
                'export',
            ],

            'attendance' => [
                'view',
                'create',
                'edit',
                'delete',
                'export',
            ],

            'salary' => [
                'view',
                'create',
                'edit',
                'delete',
                'pay',
                'export',
            ],

            'issues' => [
                'view',
                'create',
                'edit',
                'assign',
                'resolve',
                'close',
                'reopen',
                'export',
            ],

            'notifications' => [
                'view',
                'send',
                'delete',
            ],

            'reports' => [
                'view',
                'export',
            ],

            'users' => [
                'view',
                'create',
                'edit',
                'delete',
                'block',
                'reset-password',
            ],

            'roles' => [
                'view',
                'create',
                'edit',
                'delete',
                'assign-permissions',
            ],

            'settings' => [
                'view',
                'edit',
            ],

            'audit-logs' => [
                'view',
                'export',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [];

        foreach ($modules as $module => $actions) {

            foreach ($actions as $action) {

                $name = ucwords(
                    str_replace(
                        ['-', '_'],
                        ' ',
                        $module
                    )
                );

                $actionName = ucwords(
                    str_replace(
                        ['-', '_'],
                        ' ',
                        $action
                    )
                );

                $permission = Permission::updateOrCreate(
                    [
                        'slug' => $module . '.' . $action,
                    ],
                    [
                        'name' => $name . ' ' . $actionName,
                        'module' => $module,
                        'action' => $action,
                    ]
                );

                $permissions[$permission->slug] = $permission;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create Roles
        |--------------------------------------------------------------------------
        */

        $roles = [

            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access.',
            ],

            'manager' => [
                'name' => 'Manager',
                'description' => 'Business operations and management access.',
            ],

            'sales' => [
                'name' => 'Sales',
                'description' => 'Leads, customers, quotations and sales operations.',
            ],

            'production' => [
                'name' => 'Production',
                'description' => 'Design, production, batches and inventory operations.',
            ],

            'delivery' => [
                'name' => 'Delivery',
                'description' => 'Delivery and dispatch operations.',
            ],

            'accountant' => [
                'name' => 'Accountant',
                'description' => 'Payments, invoices and financial operations.',
            ],

            'hr' => [
                'name' => 'HR',
                'description' => 'Staff, attendance and salary management.',
            ],

            'customer' => [
                'name' => 'Customer',
                'description' => 'Customer portal access.',
            ],
        ];

        foreach ($roles as $slug => $data) {

            Role::updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin = All Permissions
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::where(
            'slug',
            'super-admin'
        )->firstOrFail();

        $superAdmin->permissions()->sync(
            collect($permissions)->pluck('id')->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        $manager = Role::where(
            'slug',
            'manager'
        )->firstOrFail();

        $managerPermissions = [
            'dashboard.view',
            'dashboard.export',

            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'leads.followup',
            'leads.convert',

            'customers.view',
            'customers.create',
            'customers.edit',

            'products.view',
            'pricing.view',

            'designs.view',
            'designs.approve',
            'designs.reject',

            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.send',
            'quotations.approve',
            'quotations.convert',

            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.approve',
            'orders.reject',

            'recurring-plans.view',
            'recurring-plans.create',
            'recurring-plans.edit',
            'recurring-plans.pause',
            'recurring-plans.resume',

            'daily-orders.view',
            'daily-orders.confirm',

            'deliveries.view',
            'deliveries.create',
            'deliveries.assign',
            'deliveries.dispatch',
            'deliveries.complete',

            'batches.view',
            'batches.trace',

            'inventory.view',

            'suppliers.view',
            'purchases.view',

            'payments.view',
            'invoices.view',

            'income-expenses.view',

            'issues.view',
            'issues.create',
            'issues.assign',
            'issues.resolve',

            'reports.view',
            'reports.export',
        ];

        $manager->permissions()->sync(
            collect($managerPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        */

        $sales = Role::where(
            'slug',
            'sales'
        )->firstOrFail();

        $salesPermissions = [
            'dashboard.view',

            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.followup',
            'leads.convert',

            'customers.view',
            'customers.create',
            'customers.edit',

            'products.view',
            'pricing.view',

            'designs.view',
            'designs.create',
            'designs.comment',

            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.send',

            'orders.view',
            'orders.create',

            'recurring-plans.view',
            'recurring-plans.create',
            'recurring-plans.edit',

            'daily-orders.view',

            'issues.view',
            'issues.create',
        ];

        $sales->permissions()->sync(
            collect($salesPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Production
        |--------------------------------------------------------------------------
        */

        $production = Role::where(
            'slug',
            'production'
        )->firstOrFail();

        $productionPermissions = [
            'dashboard.view',

            'products.view',

            'designs.view',
            'designs.edit',
            'designs.approve',
            'designs.reject',

            'orders.view',
            'orders.approve',

            'daily-orders.view',
            'daily-orders.confirm',

            'batches.view',
            'batches.create',
            'batches.edit',
            'batches.close',
            'batches.trace',

            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.adjust',
            'inventory.transfer',

            'issues.view',
            'issues.create',
            'issues.edit',
            'issues.resolve',
        ];

        $production->permissions()->sync(
            collect($productionPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Delivery
        |--------------------------------------------------------------------------
        */

        $delivery = Role::where(
            'slug',
            'delivery'
        )->firstOrFail();

        $deliveryPermissions = [
            'dashboard.view',

            'customers.view',

            'orders.view',

            'daily-orders.view',

            'deliveries.view',
            'deliveries.create',
            'deliveries.edit',
            'deliveries.assign',
            'deliveries.dispatch',
            'deliveries.complete',

            'batches.view',
            'batches.trace',

            'issues.view',
            'issues.create',
        ];

        $delivery->permissions()->sync(
            collect($deliveryPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Accountant
        |--------------------------------------------------------------------------
        */

        $accountant = Role::where(
            'slug',
            'accountant'
        )->firstOrFail();

        $accountantPermissions = [
            'dashboard.view',
            'dashboard.export',

            'customers.view',
            'orders.view',

            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.verify',
            'payments.refund',

            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.send',
            'invoices.mark-paid',
            'invoices.cancel',
            'invoices.download',

            'income-expenses.view',
            'income-expenses.create',
            'income-expenses.edit',
            'income-expenses.delete',

            'reports.view',
            'reports.export',
        ];

        $accountant->permissions()->sync(
            collect($accountantPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | HR
        |--------------------------------------------------------------------------
        */

        $hr = Role::where(
            'slug',
            'hr'
        )->firstOrFail();

        $hrPermissions = [
            'dashboard.view',

            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',

            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.export',

            'salary.view',
            'salary.create',
            'salary.edit',
            'salary.pay',
            'salary.export',

            'reports.view',
            'reports.export',
        ];

        $hr->permissions()->sync(
            collect($hrPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        |
        | Customer portal permissions only.
        |--------------------------------------------------------------------------
        */

        $customer = Role::where(
            'slug',
            'customer'
        )->firstOrFail();

        $customerPermissions = [
            'dashboard.view',
            'orders.view',
            'orders.create',
            'designs.view',
            'designs.create',
            'designs.approve',
            'designs.reject',
            'invoices.view',
            'invoices.download',
            'issues.view',
            'issues.create',
        ];

        $customer->permissions()->sync(
            collect($customerPermissions)
                ->map(fn ($slug) => $permissions[$slug]->id)
                ->toArray()
        );
    }
}