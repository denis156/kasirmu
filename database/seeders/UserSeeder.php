<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@kasirmu.com',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
            ],
            [
                'name' => 'Kasir 1',
                'email' => 'kasir1@kasirmu.com',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
            ],
            [
                'name' => 'Kasir 2',
                'email' => 'kasir2@kasirmu.com',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@kasirmu.com',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}