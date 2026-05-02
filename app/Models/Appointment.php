<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'client_id',       // Campo para o ID do cliente associado ao agendamento
    'availability_id', // Campo para o ID da disponibilidade associada ao agendamento
    'status',          // Campo para o status do agendamento (ex: pending, confirmed, canceled)
    'notes'            // Campo para anotações adicionais sobre o agendamento
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['client_id', 'availability_id', 'status', 'notes'];

    // Definindo as relações entre Appointment e Client. Cada agendamento pertence a um cliente.
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Definindo as relações entre Appointment e Availability. Cada agendamento está associado a uma disponibilidade.
    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    // Definindo as relações entre Appointment e Service. Cada agendamento pode ter vários serviços.
    public function services()
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }

    // Definindo os escopos para filtrar os agendamentos por status, facilitando a consulta de agendamentos pendentes, confirmados ou cancelados.
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    // Definindo o escopo para filtrar os agendamentos confirmados.
    public function scopeConfirmed(Builder $query): void
    {
        $query->where('status', 'confirmed');
    }

    // Definindo o escopo para filtrar os agendamentos cancelados.
    public function scopeCanceled(Builder $query): void
    {
        $query->where('status', 'canceled');
    }

    // Definindo um acessor para calcular o preço total dos serviços associados a um agendamento, somando os preços de todos os serviços relacionados.
    public function getTotalPriceAttribute(): float
    {
        return $this->services->sum('price');
    }
}
