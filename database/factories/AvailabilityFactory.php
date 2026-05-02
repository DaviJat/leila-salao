<?php

namespace Database\Factories;

use App\Models\Availability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'hour' => sprintf('%02d:00:00', $this->faker->numberBetween(8, 18)),
            'is_available' => true,
        ];
    }
}
