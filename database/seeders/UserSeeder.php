<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin MotoCare',
            'email' => 'admin@motocare.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Budi Mekanik',
            'email' => 'mekanik@motocare.test',
            'password' => Hash::make('password'),
            'role' => 'mechanic',
            'specialization' => 'mekanik_4_tak',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Andi Kurir',
            'email' => 'kurir@motocare.test',
            'password' => Hash::make('password'),
            'role' => 'courier',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Customer Test',
            'email' => 'customer@motocare.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);
    }
}