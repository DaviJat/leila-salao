<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Availability;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // Exibe o formulário de agendamento, e também o formulário de edição se um edit_id for passado
    public function create(Request $request)
    {
        $services = Service::all();

        // Busca apenas os horários disponíveis a partir de hoje, e se for hoje, apenas horários futuros
        $availabilities = Availability::where('is_available', true)
            ->where(function ($query) {
                $query->where('date', '>', Carbon::today())
                    ->orWhere(function ($q) {
                        $q->where('date', '=', Carbon::today())
                            ->where('hour', '>', Carbon::now()->format('H:i:s'));
                    });
            })
            ->orderBy('date')
            ->orderBy('hour')
            ->get();

        // Agrupa os horários disponíveis por data, e formata a hora para "HH:MM"
        $groupedSlots = $availabilities->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        })->map(function ($group) {
            return $group->pluck('hour')->map(fn($time) => substr($time, 0, 5));
        });

        $loggedClient = Auth::guard('clients')->user();
        $editingAppointment = null;
        $clientUpcomingAppointments = [];

        if ($loggedClient) {
            // Se tiver um edit_id na query e o cliente estiver logado, busca o agendamento para edição
            if ($request->has('edit_id')) {
                $editingAppointment = Appointment::with(['services', 'availability'])
                    ->where('client_id', $loggedClient->id)
                    ->findOrFail($request->edit_id);

                // Normaliza para evitar dupla especificação de hora
                $availability = $editingAppointment->availability;

                $datePart = $availability->date;
                if (is_object($datePart) && method_exists($datePart, 'format')) {
                    $dateString = $datePart->format('Y-m-d');
                } else {
                    $dateString = substr((string) $datePart, 0, 10);
                }

                $hourPart = (string) $availability->hour;
                $hourString = substr($hourPart, 0, 8);

                $agendamentoDate = Carbon::parse($dateString . ' ' . $hourString);

                // Calcula a diferença em horas. Se for menor que 48h, bloqueia a edição.
                if (now()->diffInHours($agendamentoDate, false) < 48) {
                    return redirect()->route('clients.appointments');
                }
            }

            // Busca os agendamentos futuros para a inteligência de calendário (evitar duplicações na mesma semana)
            $clientUpcomingAppointments = Appointment::with(['services', 'availability'])
                ->where('client_id', $loggedClient->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereHas('availability', function ($query) {
                    $query->where('date', '>=', Carbon::today());
                })
                ->get();
        }

        return Inertia::render('Appointment/Schedule', [
            'dbServices' => $services,
            'availableSlots' => $groupedSlots,
            'loggedClient' => $loggedClient,
            'editingAppointment' => $editingAppointment,
            'clientUpcomingAppointments' => $clientUpcomingAppointments,
            'flash' => [
                'success' => session('success')
            ]
        ]);
    }

    // Cria um novo agendamento, ou atualiza um existente se um edit_id for passado, seguindo a mesma lógica de validação e criação/atualização
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'whatsapp' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'services' => 'required|array|min:1',
        ]);

        // Se o cliente já estiver logado, usa o ID dele. Caso contrário, valida o OTP e loga o cliente automaticamente
        if (Auth::guard('clients')->check()) {
            $client = Auth::guard('clients')->user();
        } else {
            $request->validate(['otp' => 'required|string']);

            $client = Client::where('phone', $request->whatsapp)
                ->where('otp_code', $request->otp)
                ->where('otp_expires_at', '>', now())
                ->first();

            if (!$client) {
                return back()->withErrors(['otp' => 'Código inválido ou expirado. Tente novamente.']);
            }

            $client->update([
                'full_name' => $request->name,
                'otp_code' => null,
            ]);

            Auth::guard('clients')->login($client, true);
        }

        // Busca a disponibilidade selecionada, garantindo que ainda esteja disponível
        $availability = Availability::where('date', $request->date)
            ->where('hour', $request->time . ':00')
            ->where('is_available', true)
            ->firstOrFail();

        // Cria o agendamento e associa os serviços selecionados
        $appointment = Appointment::create([
            'client_id' => $client->id,
            'availability_id' => $availability->id,
            'status' => 'pending',
            'notes' => 'Agendamento via site',
        ]);

        // Pluck dos IDs dos serviços selecionados e attach na tabela pivot
        $serviceIds = collect($request->services)->pluck('id');
        $appointment->services()->attach($serviceIds);

        // Marca a disponibilidade como indisponível
        $availability->update(['is_available' => false]);

        return redirect()->route('agendar')->with('success', 'Agendamento confirmado com sucesso!');
    }

    // Atualiza um agendamento existente, seguindo a mesma lógica de criação, mas liberando a disponibilidade antiga e marcando a nova como indisponível
    public function update(Request $request, int $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'services' => 'required|array|min:1',
        ]);

        // Busca o agendamento do cliente logado para edição, garantindo que ele seja o dono do agendamento
        $appointment = Appointment::where('client_id', Auth::guard('clients')->id())->findOrFail($id);

        // Libera a disponibilidade antiga, marcando como disponível novamente
        $oldAvailability = Availability::find($appointment->availability_id);
        if ($oldAvailability) {
            $oldAvailability->update(['is_available' => true]);
        }

        // Busca a nova disponibilidade selecionada, garantindo que ainda esteja disponível
        $newAvailability = Availability::where('date', $request->date)
            ->where('hour', $request->time . ':00')
            ->where('is_available', true)
            ->firstOrFail();

        // Atualiza o agendamento com a nova disponibilidade e status "pending" (para reavaliação do admin)
        $appointment->update([
            'availability_id' => $newAvailability->id,
            'status' => 'pending'
        ]);

        // Pluck dos IDs dos serviços selecionados e sync na tabela pivot (remove os antigos e adiciona os novos
        $serviceIds = collect($request->services)->pluck('id');
        $appointment->services()->sync($serviceIds);

        // Marca a nova disponibilidade como indisponível
        $newAvailability->update(['is_available' => false]);

        return redirect()->route('agendar')->with('success', 'Agendamento atualizado com sucesso!');
    }
}
