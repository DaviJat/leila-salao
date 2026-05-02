<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Availability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Cria um cliente e uma disponibilidade automaticamente se não forem informados
            'client_id' => Client::factory(),
            'availability_id' => Availability::factory(),

            // Escolhe aleatoriamente um dos status que você definiu nos scopes
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'canceled']),

            // Cria uma anotação fictícia (com 30% de chance de ser nulo/vazio)
            'notes' => $this->faker->optional(0.7)->sentence(),
        ];
    }

    /**
     * Define um "estado" específico para criar apenas agendamentos pendentes.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Define um "estado" para criar agendamentos confirmados.
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'confirmed',
        ]);
    }
}
