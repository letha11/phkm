<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Prescription>
     */
    protected $model = Prescription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $submittedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $prescriptionStatus = $this->faker->randomElement(['accepted', 'preparing', 'completed']);
        $paymentStatus = 'waiting';
        $completedAt = null;
        $paidAt = null;

        if ($prescriptionStatus === 'completed') {
            $completedAt = Carbon::instance($submittedAt)->addHours($this->faker->numberBetween(1, 48));
            $paymentStatus = $this->faker->randomElement(['waiting', 'failed', 'success']);
            if ($paymentStatus === 'success') {
                $paidAt = Carbon::instance($completedAt)->addMinutes($this->faker->numberBetween(5, 60));
            }
        }

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => User::where('role', 'dokter')->inRandomOrder()->first()->id ?? User::factory(['role' => 'dokter']),
            'symptom' => $this->faker->paragraph(),
            'prescription_status' => $prescriptionStatus,
            'payment_status' => $paymentStatus,
            'consultation_fee' => $this->faker->randomElement([null, $this->faker->randomFloat(2, 5, 50)]),
            'ppn_rate_applied' => $this->faker->randomElement([null, 0.11]), // Assuming 11% PPN
            'total_amount' => null, // This would typically be calculated
            'paid_amount' => null, // This would typically be set upon payment
            'payment_method' => $paymentStatus === 'success' ? $this->faker->randomElement(['cash', 'card']) : null,
            'notes_pharmacist' => $this->faker->optional()->sentence(),
            'submitted_at' => $submittedAt,
            'completed_at' => $completedAt,
            'paid_at' => $paidAt,
        ];
    }
}
