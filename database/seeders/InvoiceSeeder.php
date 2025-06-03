<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Prescription;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paidPrescriptions = Prescription::where('payment_status', 'success')->get();

        if ($paidPrescriptions->isEmpty()) {
            $this->command->info('No paid prescriptions found, skipping invoice seeding.');
            return;
        }

        foreach ($paidPrescriptions as $prescription) {
            // Ensure an invoice doesn't already exist for this prescription
            if (Invoice::where('prescription_id', $prescription->id)->doesntExist()) {
                Invoice::factory()->create([
                    'prescription_id' => $prescription->id,
                    // Other fields like total_amount, consultation_fee will be pulled from prescription in factory or calculated
                ]);
            }
        }
    }
}
