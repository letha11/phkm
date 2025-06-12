<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'date_of_birth' => '1990-01-01',
        ]);
        $admin->assignRole(User::ROLE_ADMIN);

        $doctor = User::create([
            'name' => 'Doctor User',
            'username' => 'doctor',
            'password' => Hash::make('password'),
            'date_of_birth' => '1985-05-15',
        ]);
        $doctor->assignRole(User::ROLE_DOCTOR);

        $pharmacist = User::create([
            'name' => 'Pharmacist User',
            'username' => 'apoteker',
            'password' => Hash::make('password'),
            'date_of_birth' => '1992-08-20',
        ]);
        $pharmacist->assignRole(User::ROLE_PHARMACIST);
    }
}
