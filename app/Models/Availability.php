<?php

namespace App\Models;

use Database\Factories\AvailabilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'date',            // Campo para o nome do serviço
    'hour',            // Campo para o horário do serviço
    'is_available'     // Campo para indicar se a disponibilidade está disponível
])]
class Availability extends Model
{
    /** @use HasFactory<AvailabilityFactory> */
    use HasFactory;

    /**
     *  Get the attributes that should be cast.
     *  @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    // Uma vaga (disponibilidade) tem apenas UM agendamento.
    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    // Busca apenas as disponibilidades que estão marcadas como disponíveis
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true);
    }

    // Busca apenas as disponibilidades que estão marcadas como indisponíveis
    public function scopeUnavailable(Builder $query): void
    {
        $query->where('is_available', false);
    }
}
