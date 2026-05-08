<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Client;
use App\Models\Service;
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
        $search = trim((string) $request->input('search', ''));
        $statusList = $request->input('status', ['pending', 'confirmed']);
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 10;
        $sortField = $request->input('sort_field', 'date');
        $sortOrder = (int) $request->input('sort_order', 1);
        $sortOrder = $sortOrder === -1 ? -1 : 1;

        if (is_string($statusList)) {
            $statusList = !empty($statusList) ? [$statusList] : [];
        } elseif (!is_array($statusList)) {
            $statusList = [];
        }

        $validSortFields = ['date', 'client_name', 'status', 'total_price'];
        $sortField = in_array($sortField, $validSortFields, true) ? $sortField : 'date';

        $appointments = Appointment::with(['client', 'availability', 'services'])
            ->join('availabilities', 'appointments.availability_id', '=', 'availabilities.id')
            ->leftJoin('clients', 'appointments.client_id', '=', 'clients.id')
            ->select('appointments.*')
            ->selectRaw('(select coalesce(sum(services.price), 0) from appointment_service inner join services on services.id = appointment_service.service_id where appointment_service.appointment_id = appointments.id) as total_price_sort')
            ->whereBetween('availabilities.date', [$startDate, $endDate])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(!empty($statusList), function ($query) use ($statusList) {
                $validStatuses = array_intersect($statusList, ['pending', 'confirmed', 'canceled', 'completed']);
                if (!empty($validStatuses)) {
                    $query->whereIn('appointments.status', $validStatuses);
                }
            })
            ->when($sortField === 'date', function ($query) use ($sortOrder) {
                $query->orderBy('availabilities.date', $sortOrder === -1 ? 'desc' : 'asc')
                    ->orderBy('availabilities.hour', $sortOrder === -1 ? 'desc' : 'asc');
            })
            ->when($sortField === 'client_name', function ($query) use ($sortOrder) {
                $query->orderBy('clients.full_name', $sortOrder === -1 ? 'desc' : 'asc')
                    ->orderBy('availabilities.date')
                    ->orderBy('availabilities.hour');
            })
            ->when($sortField === 'status', function ($query) use ($sortOrder) {
                $query->orderBy('appointments.status', $sortOrder === -1 ? 'desc' : 'asc')
                    ->orderBy('availabilities.date')
                    ->orderBy('availabilities.hour');
            })
            ->when($sortField === 'total_price', function ($query) use ($sortOrder) {
                $query->orderBy('total_price_sort', $sortOrder === -1 ? 'desc' : 'asc')
                    ->orderBy('availabilities.date')
                    ->orderBy('availabilities.hour');
            })
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($appointment) {
                $appointment->append('total_price');
                return $appointment;
            });

        $services = Service::query()
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        $availabilitiesByDate = Availability::query()
            ->available()
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('hour')
            ->get(['id', 'date', 'hour'])
            ->groupBy(fn($availability) => Carbon::parse($availability->date)->toDateString())
            ->map(fn($items) => $items->map(fn($item) => [
                'id' => $item->id,
                'hour' => Carbon::parse($item->hour)->format('H:i'),
            ])->values())
            ->toArray();

        $allClients = Client::all(['id', 'full_name', 'phone']);

        return inertia('Admin/Appointments', [
            'appointments' => $appointments,
            'services' => $services,
            'availabilitiesByDate' => $availabilitiesByDate,
            'allClients' => $allClients,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $search,
                'status' => $statusList,
                'per_page' => $perPage,
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
            ],
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
        $previousStatus = $appointment->status;
        $newStatus = $request->status;

        if ($previousStatus !== 'canceled' && $newStatus === 'canceled') {
            $appointment->availability->update(['is_available' => true]);
        }

        if ($previousStatus === 'canceled' && $newStatus !== 'canceled') {
            $appointment->availability->update(['is_available' => false]);
        }

        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'Status atualizado!');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'services' => 'required|array|min:1',
        ]);

        $appointment = Appointment::with(['client', 'availability', 'services'])->findOrFail($id);
        $newTime = strlen($request->time) == 5 ? $request->time . ':00' : $request->time;
        $previousDate = Carbon::parse($appointment->availability->date)->toDateString();
        $previousTime = Carbon::parse($appointment->availability->hour)->format('H:i:s');
        $previousServiceIds = $appointment->services->pluck('id')->sort()->values()->all();

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
