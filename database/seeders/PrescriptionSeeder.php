<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users (doctors and pharmacists) using Spatie permissions
        $doctors = User::role(User::ROLE_DOCTOR)->get();
        $pharmacists = User::role(User::ROLE_PHARMACIST)->get();

        if ($doctors->isEmpty() || $pharmacists->isEmpty()) {
            $this->command->info('No doctors or pharmacists found. Please run UserSeeder first.');
            return;
        }

        // Create some patients if they don't exist
        $patients = Patient::all();
        if ($patients->isEmpty()) {
            $patients = Patient::factory(10)->create();
        }

        // Create some medicines if they don't exist
        $medicines = Medicine::all();
        if ($medicines->isEmpty()) {
            $medicines = collect([
                Medicine::create([
                    'name' => 'Paracetamol 500mg',
                    'type' => 'tablet',
                    'price' => 2500,
                    'stock' => 100,
                    'description' => 'Obat penurun demam dan pereda nyeri',
                    'dosages' => ['1x1', '2x1', '3x1', '1x2', '2x2']
                ]),
                Medicine::create([
                    'name' => 'Amoxicillin 500mg',
                    'type' => 'capsule',
                    'price' => 5000,
                    'stock' => 50,
                    'description' => 'Antibiotik untuk infeksi bakteri',
                    'dosages' => ['1x1', '2x1', '3x1', '1x2', '2x2']
                ]),
                Medicine::create([
                    'name' => 'Promag Tablet',
                    'type' => 'tablet',
                    'price' => 3000,
                    'stock' => 75,
                    'description' => 'Obat maag dan gangguan pencernaan',
                    'dosages' => ['1x1', '2x1', '3x1', '1x2', '2x2']
                ]),
                Medicine::create([
                    'name' => 'Betadine Solution',
                    'type' => 'liquid',
                    'price' => 15000,
                    'stock' => 30,
                    'description' => 'Antiseptik untuk luka luar',
                    'dosages' => ['5ml 2x sehari', '10ml 3x sehari', '15ml 1x sehari']
                ]),
                Medicine::create([
                    'name' => 'Vitamin C 1000mg',
                    'type' => 'tablet',
                    'price' => 1500,
                    'stock' => 200,
                    'description' => 'Suplemen vitamin C',
                    'dosages' => ['1x1', '2x1', '3x1', '1x2', '2x2']
                ])
            ]);
        }

        // Create prescriptions
        // $statuses = ['accepted', 'preparing', 'completed'];
        $paymentStatuses = ['waiting', 'success', 'failed'];
        $symptoms = [
            'Demam dan batuk',
            'Sakit kepala',
            'Gangguan pencernaan',
            'Luka ringan',
            'Pilek dan flu',
            'Nyeri otot',
            'Sakit gigi',
            'Alergi kulit'
        ];

        foreach ($patients as $index => $patient) {
            $doctor = $doctors->random();
            $submittedAt = Carbon::now()->subDays(rand(0, 7))->subHours(rand(0, 23));
            
            // Determine status based on time
            // $prescriptionStatus = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
            

            $prescription = Prescription::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'symptom' => $symptoms[array_rand($symptoms)],
                'payment_status' => $paymentStatus,
                'submitted_at' => $submittedAt,
                'consultation_fee' => 50000,
                'ppn_rate_applied' => 0.11,
                'payment_method' => $paymentStatus === 'success' ? ['cash', 'card', 'transfer'][array_rand(['cash', 'card', 'transfer'])] : null,
                'paid_at' => $paymentStatus === 'success' ? $submittedAt->addHours(rand(1, 6)) : null,
                'notes_pharmacist' => $paymentStatus === 'success' ? 'Pembayaran berhasil diproses' : null,
            ]);

            // Add 1-3 prescription items per prescription
            $numItems = rand(1, 3);
            $totalMedicinePrice = 0;
            
            for ($i = 0; $i < $numItems; $i++) {
                $medicine = $medicines->random();
                $amount = rand(1, 5);
                $price = $medicine->price;
                $totalMedicinePrice += $price * $amount;

                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $medicine->id,
                    'medicine_dosage_prescribed' => $this->getRandomDosage($medicine->type),
                    'medicine_amount_prescribed' => $amount,
                    'medicine_price_at_prescription' => $price,
                ]);
            }

            // Calculate total amount
            $subtotal = $totalMedicinePrice + $prescription->consultation_fee;
            $ppnAmount = $subtotal * $prescription->ppn_rate_applied;
            $totalAmount = $subtotal + $ppnAmount;

            $prescription->update([
                'total_amount' => $totalAmount,
                'paid_amount' => $paymentStatus === 'success' ? $totalAmount : null,
            ]);
        }
    }

    /**
     * Get random dosage based on medicine type
     */
    private function getRandomDosage(?string $type): string
    {
        $dosages = [
            'tablet' => ['1x1', '2x1', '3x1', '1x2', '2x2'],
            'capsule' => ['1x1', '2x1', '3x1'],
            'liquid' => ['5ml 2x sehari', '10ml 3x sehari', '15ml 1x sehari'],
            'syrup' => ['5ml 2x sehari', '10ml 3x sehari'],
        ];

        $type = $type ?? 'tablet'; // Default to tablet if null
        $availableDosages = $dosages[$type] ?? $dosages['tablet'];
        return $availableDosages[array_rand($availableDosages)];
    }
}
