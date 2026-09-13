<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class OrdersPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $actions = ['view','create','edit','delete','approve','cancel','export','confirm','status'];
        $permissions = [];

        foreach ($actions as $action) {
            $permission = Permission::updateOrCreate(
                ['slug' => 'orders.'.$action],
                [
                    'name' => 'Orders '.ucwords($action),
                    'module' => 'orders',
                    'action' => $action,
                ]
            );
            $permissions[$permission->slug] = $permission;
        }

        $super = Role::where('slug', 'super-admin')->first();
        if ($super) {
            $super->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id')->all());
        }

        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $manager->permissions()->syncWithoutDetaching([
                $permissions['orders.view']->id,
                $permissions['orders.create']->id,
                $permissions['orders.edit']->id,
                $permissions['orders.approve']->id,
                $permissions['orders.cancel']->id,
                $permissions['orders.export']->id,
                $permissions['orders.confirm']->id,
                $permissions['orders.status']->id,
            ]);
        }

        $sales = Role::where('slug', 'sales')->first();
        if ($sales) {
            $sales->permissions()->syncWithoutDetaching([
                $permissions['orders.view']->id,
                $permissions['orders.create']->id,
                $permissions['orders.edit']->id,
                $permissions['orders.export']->id,
            ]);
        }

        $production = Role::where('slug', 'production')->first();
        if ($production) {
            $production->permissions()->syncWithoutDetaching([
                $permissions['orders.view']->id,
                $permissions['orders.status']->id,
                $permissions['orders.export']->id,
            ]);
        }

        $delivery = Role::where('slug', 'delivery')->first();
        if ($delivery) {
            $delivery->permissions()->syncWithoutDetaching([
                $permissions['orders.view']->id,
                $permissions['orders.status']->id,
                $permissions['orders.export']->id,
            ]);
        }

        $accountant = Role::where('slug', 'accountant')->first();
        if ($accountant) {
            $accountant->permissions()->syncWithoutDetaching([
                $permissions['orders.view']->id,
                $permissions['orders.export']->id,
            ]);
        }
    }
}
