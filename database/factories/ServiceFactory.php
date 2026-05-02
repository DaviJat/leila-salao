<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => 'Serviço de alta qualidade realizado por nossos especialistas.',
            'price' => $this->faker->randomFloat(2, 50, 200),
            'duration_minutes' => 60,
        ];
    }
}
