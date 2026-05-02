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

    // Exibe o formulário de agendamento
    public function create()
    {
        $services = Service::all();

        // Busca horários a partir de hoje
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

        // Agrupa os horários por data para facilitar a exibição no frontend
        $groupedSlots = $availabilities->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        })->map(function ($group) {
            return $group->pluck('hour')->map(fn($time) => substr($time, 0, 5));
        });

        // Obtém o cliente logado, se houver
        $loggedClient = Auth::guard('clients')->user();

        // Renderiza a página de agendamento com os serviços, horários disponíveis e informações do cliente logado
        return Inertia::render('Appointment/Schedule', [
            'dbServices' => $services,
            'availableSlots' => $groupedSlots,
            'loggedClient' => $loggedClient,
            'flash' => [
                'success' => session('success')
            ]
        ]);
    }

    // Processa o formulário de agendamento
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'whatsapp' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'services' => 'required|array|min:1',
        ]);

        // Verifica se o cliente já está autenticado ou se precisa validar o OTP
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

        // Verifica se o horário ainda está disponível
        $availability = Availability::where('date', $request->date)
            ->where('hour', $request->time . ':00')
            ->where('is_available', true)
            ->firstOrFail();

        // Cria o agendamento
        $appointment = Appointment::create([
            'client_id' => $client->id,
            'availability_id' => $availability->id,
            'status' => 'pending',
            'notes' => 'Agendamento via site',
        ]);

        $serviceIds = collect($request->services)->pluck('id');
        $appointment->services()->attach($serviceIds);

        $availability->update(['is_available' => false]);

        return redirect()->route('agendar')->with('success', 'Agendamento confirmado com sucesso!');
    }
}
