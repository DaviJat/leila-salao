<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Client;
use App\Models\Service;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();

        $startDate = $request->input('start_date', $now->copy()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', $now->copy()->endOfWeek()->format('Y-m-d'));

        $sortBy = $request->input('sort_by', 'date');
        $sortDir = $request->input('sort_dir', 'asc');

        $query = Appointment::with(['client', 'services', 'availability'])
            ->whereHas('availability', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            });

        if ($sortBy === 'client') {
            $query->join('clients', 'appointments.client_id', '=', 'clients.id')
                ->orderBy('clients.full_name', $sortDir)
                ->select('appointments.*');
        } elseif ($sortBy === 'status') {
            $query->orderBy('status', $sortDir);
        } else {
            $query->join('availabilities', 'appointments.availability_id', '=', 'availabilities.id')
                ->orderBy('availabilities.date', $sortDir)
                ->orderBy('availabilities.hour', $sortDir)
                ->select('appointments.*');
        }

        $appointments = $query->get();

        $availabilities = Availability::where('is_available', true)
            ->where(function ($query) {
                $query->where('date', '>', Carbon::today())
                    ->orWhere(function ($q) {
                        $q->where('date', '=', Carbon::today())
                            ->where('hour', '>', Carbon::now()->format('H:i:s'));
                    });
            })
            ->orderBy('date')->orderBy('hour')->get();

        $availableSlots = $availabilities->groupBy(function ($item) {
            return $item->date->format('Y-m-d');
        })->map(function ($group) {
            return $group->pluck('hour')->map(fn($time) => substr($time, 0, 5));
        });

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
            'dbServices' => Service::all(),
            'availableSlots' => $availableSlots,
            'allClients' => Client::all(['id', 'full_name', 'phone']), // <-- ENVIANDO CLIENTES PARA BUSCA
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string',
            'client_phone' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'services' => 'required|array|min:1',
        ]);

        $client = Client::firstOrCreate(
            ['phone' => preg_replace('/\D/', '', $request->client_phone)],
            ['full_name' => $request->client_name]
        );

        $availability = Availability::where('date', $request->date)
            ->where('hour', $request->time . ':00')
            ->where('is_available', true)
            ->firstOrFail();

        $appointment = Appointment::create([
            'client_id' => $client->id,
            'availability_id' => $availability->id,
            'status' => 'confirmed',
            'notes' => 'Agendado internamente por Leila',
        ]);

        $serviceIds = collect($request->services)->pluck('id');
        $appointment->services()->attach($serviceIds);
        $availability->update(['is_available' => false]);

        return back()->with('success', 'Agendamento criado!');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:pending,confirmed,canceled,completed']);

        $appointment = Appointment::with(['client', 'availability'])->findOrFail($id);
        $appointment->update(['status' => $request->status]);

        if ($request->status === 'canceled') {
            $appointment->availability->update(['is_available' => true]);
        }

        return back()->with('success', 'Status atualizado!');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'services' => 'required|array|min:1',
        ]);

        $appointment = Appointment::with('availability')->findOrFail($id);
        $newTime = strlen($request->time) == 5 ? $request->time . ':00' : $request->time;

        // Se a Leila alterou o dia ou a hora, atualizamos a disponibilidade no banco
        if ($appointment->availability->date != $request->date || $appointment->availability->hour != $newTime) {

            // 1. Libera o slot antigo
            $appointment->availability->update(['is_available' => true]);

            // 2. Procura e bloqueia o novo slot
            $newAvailability = Availability::where('date', $request->date)
                ->where('hour', $newTime)
                ->where('is_available', true)
                ->firstOrFail();

            $newAvailability->update(['is_available' => false]);
            $appointment->availability_id = $newAvailability->id;
            $appointment->save();
        }

        // Atualiza os serviços (adiciona/remove do banco)
        $serviceIds = collect($request->services)->pluck('id');
        $appointment->services()->sync($serviceIds);

        return back()->with('success', 'Agendamento atualizado com sucesso!');
    }
}
