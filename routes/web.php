<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\AvailabilityController as AdminAvailabilityController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rota para a página inicial (Home)
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Admin dashboard
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

// Rotas do Painel Administrativo (Protegidas por autenticação)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/agendamentos', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/agendamentos', [AdminAppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/agendamentos/{id}', [AdminAppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/agendamentos/{id}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.status');

    // Clientes
    Route::get('/clientes', [AdminClientController::class, 'index'])->name('clients.index');
    Route::post('/clientes', [AdminClientController::class, 'store'])->name('clients.store');
    Route::patch('/clientes/{id}', [AdminClientController::class, 'update'])->name('clients.update');

    // Serviços
    Route::get('/servicos', [AdminServiceController::class, 'index'])->name('services.index');
    Route::post('/servicos', [AdminServiceController::class, 'store'])->name('services.store');
    Route::patch('/servicos/{id}', [AdminServiceController::class, 'update'])->name('services.update');
    Route::delete('/servicos/{id}', [AdminServiceController::class, 'destroy'])->name('services.destroy');

    // Horários Disponíveis
    Route::get('/horarios', [AdminAvailabilityController::class, 'index'])->name('availabilities.index');
    Route::post('/horarios', [AdminAvailabilityController::class, 'store'])->name('availabilities.store');
    Route::post('/horarios/padrao', [AdminAvailabilityController::class, 'storePattern'])->name('availabilities.pattern');
    Route::patch('/horarios/{id}/status', [AdminAvailabilityController::class, 'updateStatus'])->name('availabilities.updateStatus');
    Route::delete('/horarios/{id}', [AdminAvailabilityController::class, 'destroy'])->name('availabilities.destroy');
});

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
