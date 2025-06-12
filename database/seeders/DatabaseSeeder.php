<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PatientSeeder::class,
            MedicineSeeder::class,
            PrescriptionSeeder::class, // Prescriptions depend on Users and Patients
            PrescriptionItemSeeder::class, // PrescriptionItems depend on Prescriptions and Medicines
            InvoiceSeeder::class, // Invoices depend on paid Prescriptions
        ]);
    }
}
