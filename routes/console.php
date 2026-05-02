<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Availability;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Atualizar a disponibilidade dos horários passados automaticamente
Schedule::call(function () {
    // Atualiza para false todos os horários que ainda estão 'true' mas já passaram
    Availability::where('is_available', true)
        ->where(function ($query) {
            // Regra 1: Dias anteriores a hoje
            $query->where('date', '<', Carbon::today())
                // Regra 2: Hoje, mas a hora já passou
                ->orWhere(function ($q) {
                    $q->where('date', '=', Carbon::today())
                        ->where('hour', '<=', Carbon::now()->format('H:i:s'));
                });
        })
        ->update(['is_available' => false]);
})->everyMinute(); // O Laravel vai rodar essa verificação a cada 1 minuto