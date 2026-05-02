<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rota para a página inicial (Home)

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

// Rotas de Dashboard (Protegidas por autenticação e verificação de email)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rotas de Perfil (Protegidas por autenticação)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rotas de Agendamento (Públicas)

// Rota para a página de agendamento (formulário)
Route::get('/agendar', function () {
    return Inertia::render('Appointment/Schedule');
})->name('agendar');

// Rotas de Agendamento
Route::get('/agendar', [AppointmentController::class, 'create'])->name('agendar');
Route::post('/agendamentos', [AppointmentController::class, 'store'])->name('appointments.store');

// Rotas do Cliente
Route::post('/agendamentos/enviar-otp', [ClientController::class, 'sendOtp'])->name('appointments.sendOtp');
Route::post('/logout-cliente', [ClientController::class, 'logout'])->name('clients.logout');

require __DIR__ . '/auth.php';
