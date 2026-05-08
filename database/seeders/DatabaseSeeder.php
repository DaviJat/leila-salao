<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Availability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Desativa a emissão de eventos de modelo para otimizar o tempo de execução da seed.
     */
    use WithoutModelEvents;

    /**
     * Executa as seeds do banco de dados.
     */
    public function run(): void
    {
        // Criação da conta de administrador do sistema
        User::factory()->create([
            'name' => 'Leila Admin',
            'email' => 'leila@example.com',
            'role' => 'admin',
        ]);

        // Criação de usuários secundários para testes de permissão
        User::factory(2)->create();

        // Definição do catálogo de serviços com escopo real de preços e durações
        $services = Service::factory()
            ->count(7)
            ->state(new Sequence(
                ['name' => 'Corte', 'price' => 80.00, 'duration_minutes' => 45],
                ['name' => 'Coloração', 'price' => 250.00, 'duration_minutes' => 150],
                ['name' => 'Tratamento Capilar', 'price' => 120.00, 'duration_minutes' => 60],
                ['name' => 'Penteado', 'price' => 180.00, 'duration_minutes' => 90],
                ['name' => 'Escova Modeladora', 'price' => 60.00, 'duration_minutes' => 40],
                ['name' => 'Manicure', 'price' => 100.00, 'duration_minutes' => 30],
                ['name' => 'Pedicure', 'price' => 110.00, 'duration_minutes' => 30],
            ))
            ->create();

        // Geração da base de clientes fictícios
        $clients = Client::factory(50)->create();

        // Configuração do período de geração: Primeiro dia do mês atual até o último dia do mês seguinte
        $today = Carbon::today();
        $startDate = $today->copy()->startOfMonth();
        $endDate = $today->copy()->addMonth()->endOfMonth();

        // Iteração diária para montagem da grade de horários e agendamentos
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            // Regra de negócio: Salão não tem expediente aos domingos
            if ($date->isSunday()) {
                continue;
            }

            $hours = [];

            // Regra de negócio: Expediente em dias úteis (Segunda a Sexta)
            if ($date->isWeekday()) {
                $hours = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
            }
            // Regra de negócio: Expediente aos Sábados (Apenas período matutino)
            elseif ($date->isSaturday()) {
                $hours = ['08:00', '09:00', '10:00', '11:00'];
            }

            foreach ($hours as $hour) {
                // Registro do horário (slot) na tabela de disponibilidades
                $availability = Availability::create([
                    'date' => $date->format('Y-m-d'),
                    'hour' => $hour . ':00',
                    'is_available' => true,
                ]);

                // Simulação da taxa de ocupação: Maior probabilidade no mês vigente, menor no mês seguinte
                $ocupationChance = 0;
                if ($date->month === $today->month) {
                    $ocupationChance = 75;
                } elseif ($date->month === $today->copy()->addMonth()->month) {
                    $ocupationChance = 30;
                }

                // Efetivação do agendamento mediante a probabilidade calculada
                if (rand(1, 100) <= $ocupationChance) {

                    // Determinação contextual do status do agendamento
                    $status = 'pending';
                    if ($date->lt($today)) {
                        $status = 'completed';
                    } elseif ($date->eq($today)) {
                        $status = rand(1, 100) <= 80 ? 'confirmed' : 'pending';
                    } else {
                        $status = rand(1, 100) <= 50 ? 'confirmed' : 'pending';

                        // Simulação de taxa de cancelamento prévio para datas futuras (5% de chance)
                        if (rand(1, 100) <= 5) {
                            $status = 'canceled';
                        }
                    }

                    // Vínculo do agendamento a um cliente aleatório
                    $appointment = Appointment::factory()->create([
                        'client_id' => $clients->random()->id,
                        'availability_id' => $availability->id,
                        'status' => $status,
                    ]);

                    // Associação da tabela pivot (Appointment x Service) com 1 a 2 serviços
                    $appointment->services()->attach(
                        $services->random(rand(1, 2))->pluck('id')->toArray()
                    );

                    // Bloqueio da disponibilidade caso o agendamento esteja ativo
                    if ($status !== 'canceled') {
                        $availability->update(['is_available' => false]);
                    }
                }
            }
        }
    }
}
