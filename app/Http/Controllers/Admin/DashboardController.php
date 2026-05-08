<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Processa os dados do dashboard administrativo.
     */
    public function index(Request $request)
    {
        // Define o período de análise com base nos filtros fornecidos ou usa o mês atual como padrão.
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Consulta os agendamentos dentro do período selecionado, incluindo as relações necessárias.
        $appointments = Appointment::with(['availability', 'services'])
            ->whereHas('availability', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })->get();

        // Cálculo dos KPIs principais.
        $totalAgendamentos = $appointments->count();
        $pendentes = $appointments->where('status', 'pending')->count();
        $confirmados = $appointments->where('status', 'confirmed')->count();
        $concluidos = $appointments->where('status', 'completed')->count();

        // Determina se a análise será mensal ou diária com base na diferença entre as datas.
        $diffDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
        $isMonthly = $diffDays > 60;

        $receitaPorPeriodo = [];

        if ($isMonthly) {
            $period = CarbonPeriod::create(Carbon::parse($startDate)->startOfMonth(), '1 month', Carbon::parse($endDate)->startOfMonth());
            foreach ($period as $date) {
                $receitaPorPeriodo[$date->format('m/Y')] = 0;
            }
        } else {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $receitaPorPeriodo[$date->format('d/m')] = 0;
            }
        }

        $receitaTotal = 0;
        $servicosCount = collect();

        // Processamento dos dados de receita e contagem de serviços realizados.
        foreach ($appointments as $app) {
            if (in_array($app->status, ['confirmed', 'completed'])) {
                if ($app->availability) {
                    $date = Carbon::parse($app->availability->date);

                    $key = $isMonthly ? $date->format('m/Y') : $date->format('d/m');

                    if (array_key_exists($key, $receitaPorPeriodo)) {
                        $price = $app->total_price;
                        $receitaPorPeriodo[$key] += $price;
                        $receitaTotal += $price;
                    }
                }
            }

            foreach ($app->services as $service) {
                $servicosCount->push($service->name);
            }
        }

        // Identificação dos serviços mais populares.
        $topServicos = $servicosCount->countBy()->sortDesc()->take(5);

        return Inertia::render('Admin/Dashboard', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'kpis' => [
                'receitaTotal' => $receitaTotal,
                'totalAgendamentos' => $totalAgendamentos,
                'concluidos' => $concluidos,
                'confirmados' => $confirmados,
                'pendentes' => $pendentes,
            ],
            'charts' => [
                'receita' => [
                    'labels' => array_keys($receitaPorPeriodo),
                    'data' => array_values($receitaPorPeriodo),
                ],
                'servicos' => [
                    'labels' => $topServicos->keys()->toArray(),
                    'data' => $topServicos->values()->toArray(),
                ]
            ]
        ]);
    }
}
