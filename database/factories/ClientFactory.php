<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name'    => $this->faker->name(),
            'email'        => $this->faker->unique()->safeEmail(),
            'phone'        => $this->faker->cellphoneNumber(false),
            'cpf'          => $this->faker->cpf(false),
            'birth_date'   => $this->faker->date('Y-m-d', '-18 years'), // Clientes com +18 anos

            // Endereço
            'postal_code'  => $this->faker->postcode(),
            'street'       => $this->faker->streetName(),
            'number'       => $this->faker->buildingNumber(),
            'complement'   => $this->faker->secondaryAddress(),
            'neighborhood' => $this->faker->words(2, true),
            'city'         => $this->faker->city(),
            'state'        => $this->faker->stateAbbr(), // Ex: BA, SP

            // Notas e Autenticação
            'notes'        => $this->faker->sentence(),
            'otp_code'     => null, // Começa nulo até o login ser solicitado
            'otp_expires_at' => null,
        ];
    }
}
