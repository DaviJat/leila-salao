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
    // O WithoutModelEvents desativa eventos (como envio de e-mails/notificações) 
    // durante o seed, deixando o processo muito mais rápido.
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criando um usuário admin fixo para facilitar o acesso ao painel de administração, com credenciais conhecidas.
        User::factory()->create([
            'name' => 'Leila Admin',
            'email' => 'leila@example.com',
            'role' => 'admin',
        ]);

        // Criando mais 2 usuários comuns para testes, usando a factory padrão do Laravel.
        User::factory(2)->create();

        // Criando os 6 serviços exatos oferecidos pelo salão utilizando Sequence para garantir nomes, preços e durações reais.
        $services = Service::factory()
            ->count(6)
            ->state(new Sequence(
                ['name' => 'Corte e Visagismo', 'price' => 80.00, 'duration_minutes' => 45],
                ['name' => 'Coloração e Mechas', 'price' => 250.00, 'duration_minutes' => 150],
                ['name' => 'Tratamento Capilar', 'price' => 120.00, 'duration_minutes' => 60],
                ['name' => 'Penteado e Make', 'price' => 180.00, 'duration_minutes' => 90],
                ['name' => 'Escova Modeladora', 'price' => 60.00, 'duration_minutes' => 40],
                ['name' => 'Spa do Couro Cabeludo', 'price' => 100.00, 'duration_minutes' => 50],
            ))
            ->create();

        // Criando 10 clientes fictícios para preencher a tabela de clientes, cada um com dados realistas gerados pela factory.
        $clients = Client::factory(10)->create();

        // Gerando a grade de horários disponíveis para os próximos 30 dias, respeitando o horário de funcionamento do salão.
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        $createdAvailabilities = collect();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {

            // Ignorando os domingos, pois o salão estará fechado.
            if ($date->isSunday()) {
                continue;
            }

            $hours = [];

            // Definindo os horários para dias úteis (Segunda a Sexta) das 8h às 12h e das 14h às 18h.
            if ($date->isWeekday()) {
                $hours = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
            }
            // Definindo os horários para os Sábados, funcionando apenas na parte da manhã, das 8h às 12h.
            elseif ($date->isSaturday()) {
                $hours = ['08:00', '09:00', '10:00', '11:00'];
            }

            // Inserindo os horários gerados no banco de dados para o dia correspondente.
            foreach ($hours as $hour) {
                $availability = Availability::create([
                    'date' => $date->format('Y-m-d'),
                    'hour' => $hour . ':00',
                    'is_available' => true,
                ]);
                $createdAvailabilities->push($availability);
            }
        }

        // Criando de 1 a 3 agendamentos para cada cliente criado, associando cada agendamento a um cliente e a uma disponibilidade real recém-criada, e atribuindo de 1 a 3 serviços.
        foreach ($clients as $client) {

            // Pegando uma disponibilidade aleatória que ainda esteja marcada como livre.
            $slot = $createdAvailabilities->where('is_available', true)->random();

            $appointment = Appointment::factory()->create([
                'client_id' => $client->id,
                'availability_id' => $slot->id,
            ]);

            // Associando de 1 a 3 serviços aleatórios da nossa lista para este agendamento na tabela pivot.
            $appointment->services()->attach(
                $services->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Atualizando o status da disponibilidade no banco de dados para ocupado (is_available = false).
            $slot->update(['is_available' => false]);

            // Atualizando a nossa collection em memória para garantir que este horário não seja pego por outro cliente no loop.
            $createdAvailabilities = $createdAvailabilities->map(function ($item) use ($slot) {
                if ($item->id === $slot->id) {
                    $item->is_available = false;
                }
                return $item;
            });
        }
    }
}
