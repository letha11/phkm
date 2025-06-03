<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Medicine>
     */
    protected $model = Medicine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' ' . $this->faker->randomElement(['Syrup', 'Tablet', 'Capsule', 'Ointment']),
            'stock' => $this->faker->numberBetween(0, 200),
            'dosages' => $this->faker->randomElements(['10mg', '20mg', '50mg', '100mg', '5ml', '10ml'], $this->faker->numberBetween(1, 3)),
            'price' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
