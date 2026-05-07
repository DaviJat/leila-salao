<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // default period: current month
        $now = Carbon::now();
        $start = $request->input('start_date', $now->copy()->startOfMonth()->format('Y-m-d'));
        $end = $request->input('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));

        $earnings = $this->aggregateEarnings($start, $end, 'day');
        $topServices = $this->topServices($start, $end);
        $appointmentsOverview = $this->appointmentsOverview($start, $end);

        return inertia('Admin/Dashboard', [
            'earnings' => $earnings,
            'topServices' => $topServices,
            'appointmentsOverview' => $appointmentsOverview,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
            ],
        ]);
    }

    public function data(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $group = $request->input('group', 'day'); // day|month|year

        $earnings = $this->aggregateEarnings($start, $end, $group);
        $topServices = $this->topServices($start, $end);
        $overview = $this->appointmentsOverview($start, $end);

        return response()->json([
            'earnings' => $earnings,
            'topServices' => $topServices,
            'overview' => $overview,
        ]);
    }

    protected function aggregateEarnings($start, $end, $group = 'day')
    {
        $format = match ($group) {
            'month' => 'YYYY-MM',
            'year' => 'YYYY',
            default => 'YYYY-MM-DD',
        };

        $rows = Appointment::query()
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->join('services', 'appointment_service.service_id', '=', 'services.id')
            ->join('availabilities', 'appointments.availability_id', '=', 'availabilities.id')
            ->whereBetween('availabilities.date', [$start, $end])
            ->where('appointments.status', 'completed')
            ->selectRaw("TO_CHAR(availabilities.date, '{$format}') as period, SUM(services.price) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn($r) => ['period' => $r->period, 'total' => (float) $r->total]);

        return $rows->values();
    }

    protected function topServices($start, $end, $limit = 6)
    {
        $rows = Appointment::query()
            ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
            ->join('services', 'appointment_service.service_id', '=', 'services.id')
            ->join('availabilities', 'appointments.availability_id', '=', 'availabilities.id')
            ->whereBetween('availabilities.date', [$start, $end])
            ->where('appointments.status', 'completed')
            ->selectRaw('services.id, services.name, COUNT(*) as performed')
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('performed')
            ->limit($limit)
            ->get();

        return $rows->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'performed' => (int) $r->performed])->values();
    }

    protected function appointmentsOverview($start, $end)
    {
        // total appointments by day in range and overall totals
        $byDay = Appointment::query()
            ->join('availabilities', 'appointments.availability_id', '=', 'availabilities.id')
            ->whereBetween('availabilities.date', [$start, $end])
            ->selectRaw("TO_CHAR(availabilities.date, 'YYYY-MM-DD') as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($r) => ['day' => $r->day, 'total' => (int) $r->total]);

        $totals = Appointment::query()
            ->join('availabilities', 'appointments.availability_id', '=', 'availabilities.id')
            ->whereBetween('availabilities.date', [$start, $end])
            ->selectRaw("SUM(CASE WHEN appointments.status = 'pending' THEN 1 ELSE 0 END)::int as pending, SUM(CASE WHEN appointments.status = 'confirmed' THEN 1 ELSE 0 END)::int as confirmed, SUM(CASE WHEN appointments.status = 'canceled' THEN 1 ELSE 0 END)::int as canceled, SUM(CASE WHEN appointments.status = 'completed' THEN 1 ELSE 0 END)::int as completed")
            ->first();

        return [
            'byDay' => $byDay->values(),
            'totals' => [
                'pending' => (int) ($totals->pending ?? 0),
                'confirmed' => (int) ($totals->confirmed ?? 0),
                'canceled' => (int) ($totals->canceled ?? 0),
                'completed' => (int) ($totals->completed ?? 0),
            ],
        ];
    }
}
