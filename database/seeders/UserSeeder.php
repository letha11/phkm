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
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'date_of_birth' => '1990-01-01',
        ]);

        User::create([
            'name' => 'Doctor User',
            'username' => 'doctor',
            'password' => Hash::make('password'),
            'role' => 'dokter',
            'date_of_birth' => '1985-05-15',
        ]);

        User::create([
            'name' => 'Pharmacist User',
            'username' => 'apoteker',
            'password' => Hash::make('password'),
            'role' => 'apoteker',
            'date_of_birth' => '1992-08-20',
        ]);
    }
}
