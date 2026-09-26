<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');

        if (!$email) {
            throw new \RuntimeException(
                'ADMIN_EMAIL is missing from .env'
            );
        }

        $password = env('ADMIN_PASSWORD');
        $mobile = env('ADMIN_MOBILE');

        if (!$password) {
            throw new \RuntimeException(
                'ADMIN_PASSWORD is missing from .env'
            );
        }

        if (!$mobile) {
            throw new \RuntimeException(
                'ADMIN_MOBILE is missing from .env'
            );
        }

        $user = User::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => env(
                    'ADMIN_NAME',
                    'Jalvan Administrator'
                ),
                'mobile' => $mobile,
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );

        $role = Role::where(
            'slug',
            'super-admin'
        )->firstOrFail();

        $user->roles()->syncWithoutDetaching([
            $role->id,
        ]);
    }
}