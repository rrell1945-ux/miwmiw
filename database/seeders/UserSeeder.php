<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('MIMIW_ADMIN_PASSWORD', 'farelgateng');
        $userPassword = env('MIMIW_USER_PASSWORD', 'salmacantik');

        $admin = User::query()->updateOrCreate(
            ['email' => 'farel@mimiw.app'],
            [
                'name' => 'Farel',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $admin->setting();

        $salma = User::query()->updateOrCreate(
            ['email' => 'salma@mimiw.app'],
            [
                'name' => 'Salma Alfatunisa',
                'password' => Hash::make($userPassword),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        $salma->setting();
    }
}
