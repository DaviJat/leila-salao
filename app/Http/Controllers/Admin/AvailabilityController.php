<?php

namespace App\Http\Controllers\Admin;

use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of availabilities with pagination and sorting.
     */
    public function index(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $query = $this->baseQuery($filters);

        $availabilities = $query->paginate($filters['per_page'])
            ->withQueryString();

        return inertia('Admin/Availabilities', [
            'availabilities' => $availabilities,
            'filters' => $filters,
        ]);
    }

    /**
     * Store newly created availabilities (bulk creation).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
            'hours' => 'required|array|min:1',
            'hours.*' => 'required|date_format:H:i',
        ], [
            'date.required' => 'A data é obrigatória.',
            'hours.required' => 'Selecione pelo menos um horário.',
            'hours.*.date_format' => 'Todos os horários devem estar no formato HH:mm.',
        ]);

        $date = $data['date'];
        $existingHours = Availability::where('date', $date)
            ->pluck('hour')
            ->map(fn($hour) => Carbon::parse($hour)->format('H:i'))
            ->toArray();

        $newAvailabilities = [];
        foreach ($data['hours'] as $hour) {
            if (!in_array($hour, $existingHours)) {
                $newAvailabilities[] = [
                    'date' => $date,
                    'hour' => $hour,
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($newAvailabilities)) {
            Availability::insert($newAvailabilities);
        }

        return redirect()->route('admin.availabilities.index')
            ->with('success', count($newAvailabilities) . ' horários adicionados com sucesso!');
    }

    /**
     * Create a monthly pattern of availabilities.
     */
    public function storePattern(Request $request)
    {
        $data = $request->validate([
            'weekdays' => 'required|array|min:1',
            'weekdays.*' => 'required|integer|between:0,6',
            'hours' => 'required|array|min:1',
            'hours.*' => 'required|date_format:H:i',
        ], [
            'weekdays.required' => 'Selecione pelo menos um dia da semana.',
            'hours.required' => 'Selecione pelo menos um horário.',
        ]);

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfMonth()->endOfDay();
        $selectedWeekdays = array_map('intval', $data['weekdays']);

        $existingSlots = Availability::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->mapWithKeys(fn(Availability $availability) => [
                $availability->date->format('Y-m-d') . '|' . Carbon::parse($availability->hour)->format('H:i') => true,
            ]);

        $newAvailabilities = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if (!in_array($date->dayOfWeek, $selectedWeekdays, true)) {
                continue;
            }

            foreach ($data['hours'] as $hour) {
                $slotKey = $date->format('Y-m-d') . '|' . $hour;

                if ($existingSlots->has($slotKey)) {
                    continue;
                }

                $newAvailabilities[] = [
                    'date' => $date->format('Y-m-d'),
                    'hour' => $hour,
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($newAvailabilities)) {
            Availability::insert($newAvailabilities);
        }

        return redirect()->route('admin.availabilities.index')
            ->with('success', count($newAvailabilities) . ' horários gerados para o mês atual!');
    }

    /**
     * Update availability status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $availability = Availability::findOrFail($id);

        $data = $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $availability->update($data);

        return back()->with('success', 'Status atualizado com sucesso!');
    }

    /**
     * Delete the specified availability.
     */
    public function destroy(int $id)
    {
        $availability = Availability::findOrFail($id);
        $availability->delete();

        return redirect()->route('admin.availabilities.index')
            ->with('success', 'Horário deletado com sucesso!');
    }

    /**
     * Build the base query with filters.
     */
    private function baseQuery(array $filters)
    {
        $query = Availability::query();

        if (!empty($filters['status'])) {
            if (count($filters['status']) === 1) {
                $status = reset($filters['status']);
                $query->where('is_available', $status === 'available' ? true : false);
            }
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::createFromFormat('Y-m-d', $filters['start_date'])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $filters['end_date'])->endOfDay();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $sortField = in_array($filters['sort_field'], ['date', 'hour', 'is_available'])
            ? $filters['sort_field']
            : 'date';

        $query->orderBy($sortField, $filters['sort_order'] === 1 ? 'asc' : 'desc');

        return $query;
    }

    /**
     * Extract and validate filters from request.
     */
    private function filtersFromRequest(Request $request)
    {
        return [
            'status' => $request->input('status', []),
            'start_date' => $request->input('start_date', ''),
            'end_date' => $request->input('end_date', ''),
            'per_page' => in_array((int) $request->input('per_page', 10), [10, 20, 50], true)
                ? (int) $request->input('per_page', 10)
                : 10,
            'sort_field' => $request->input('sort_field', 'date'),
            'sort_order' => in_array((int) $request->input('sort_order', 1), [1, -1], true)
                ? (int) $request->input('sort_order', 1)
                : 1,
        ];
    }
}
