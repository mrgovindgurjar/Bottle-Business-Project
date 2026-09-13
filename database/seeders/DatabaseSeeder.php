<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
                AdminUserSeeder::class,
                JalvanRbacSeeder::class,
                OrdersPermissionSeeder::class,
                ProductionPermissionSeeder::class,
                BatchPermissionSeeder::class,
                InventoryPermissionSeeder::class,
                SuppliersPurchasesPermissionSeeder::class,
                DeliveryPermissionSeeder::class,
                PaymentPermissionSeeder::class,
                InventoryPermissionSeeder::class,
                IncomeExpensePermissionSeeder::class,
                StaffAttendancePermissionSeeder::class
        ]);
    }
}