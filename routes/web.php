<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rota para a página inicial (Home)
Route::get('/', function () {
    return Inertia::render('Home');
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
Route::get('/agendar', [AppointmentController::class, 'create'])->name('agendar');
Route::post('/agendar', [AppointmentController::class, 'store'])->name('appointments.store');
Route::put('/agendar/{id}', [AppointmentController::class, 'update'])->name('appointments.update');

// Rotas do Cliente
Route::get('/meus-agendamentos', [ClientController::class, 'myAppointments'])->name('clients.appointments');
Route::post('/cliente/login-otp', [ClientController::class, 'loginViaOtp'])->name('clients.loginOtp');
Route::post('/cliente/enviar-otp', [ClientController::class, 'sendOtp'])->name('appointments.sendOtp');
Route::post('/cliente/logout', [ClientController::class, 'logout'])->name('clients.logout');

require __DIR__ . '/auth.php';
