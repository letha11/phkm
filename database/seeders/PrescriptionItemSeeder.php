<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Medicine;
use Illuminate\Database\Seeder;

class PrescriptionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prescriptions = Prescription::all();
        $medicines = Medicine::all();

        if ($prescriptions->isEmpty() || $medicines->isEmpty()) {
            $this->command->info('No prescriptions or medicines found, skipping prescription item seeding.');
            return;
        }

        foreach ($prescriptions as $prescription) {
            $itemCount = rand(1, 5);
            for ($i = 0; $i < $itemCount; $i++) {
                $medicine = $medicines->random();
                $dosages = $medicine->dosages; // This is already an array due to casts
                $selectedDosage = $dosages[array_rand($dosages)] ?? 'N/A';

                PrescriptionItem::factory()->create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $medicine->id,
                    'medicine_dosage_prescribed' => $selectedDosage,
                    'medicine_price_at_prescription' => $medicine->price,
                ]);
            }
        }
    }
}
