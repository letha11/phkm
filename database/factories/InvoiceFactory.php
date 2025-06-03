<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Invoice>
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prescription = Prescription::where('payment_status', 'success')
                                  ->whereDoesntHave('invoice')
                                  ->inRandomOrder()
                                  ->first();

        if (!$prescription) {
            $prescription = Prescription::factory()->create([
                'payment_status' => 'success',
                'paid_at' => Carbon::now(),
                'consultation_fee' => $this->faker->randomFloat(2, 20, 100),
                'ppn_rate_applied' => 0.11,
                'payment_method' => $this->faker->randomElement(['cash', 'card']),
            ]);
            // Create some items for this new prescription
            PrescriptionItem::factory()->count(rand(1,3))->create(['prescription_id' => $prescription->id]);
             $prescription->refresh(); // Refresh to get the items
        }

        $subtotalMedicines = 0;
        if ($prescription->prescriptionItems()->exists()) {
            foreach ($prescription->prescriptionItems as $item) {
                $subtotalMedicines += $item->medicine_amount_prescribed * $item->medicine_price_at_prescription;
            }
        }

        $consultationFee = $prescription->consultation_fee ?? $this->faker->randomFloat(2, 20, 100);
        $ppnRate = $prescription->ppn_rate_applied ?? 0.11;
        $ppnAmount = ($subtotalMedicines + $consultationFee) * $ppnRate;
        $grandTotal = $subtotalMedicines + $consultationFee + $ppnAmount;

        return [
            'prescription_id' => $prescription->id,
            'invoice_number' => 'INV-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'issue_date' => $prescription->paid_at ?? Carbon::now(),
            'subtotal_medicines' => $subtotalMedicines,
            'consultation_fee' => $consultationFee,
            'ppn_amount' => $ppnAmount,
            'grand_total' => $grandTotal,
            'payment_method' => $prescription->payment_method ?? $this->faker->randomElement(['cash', 'card']),
        ];
    }
}
