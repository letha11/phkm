<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionItem>
 */
class PrescriptionItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PrescriptionItem>
     */
    protected $model = PrescriptionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Note: prescription_id and medicine_id are typically provided during seeder.
        $medicine = Medicine::inRandomOrder()->first() ?? Medicine::factory()->create();
        $dosages = $medicine->dosages;
        $selectedDosage = $this->faker->randomElement($dosages);

        return [
            'prescription_id' => Prescription::factory(),
            'medicine_id' => $medicine->id,
            'medicine_dosage_prescribed' => $selectedDosage,
            'medicine_amount_prescribed' => $this->faker->numberBetween(1, 30),
            'medicine_price_at_prescription' => $medicine->price,
        ];
    }
}
