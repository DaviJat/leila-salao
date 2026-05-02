<?php

namespace App\Models;


use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',            // Campo para o nome do serviço
    'description',     // Campo para a descrição do serviço
    'price',           // Campo para o preço do serviço
    'duration_minutes' // Campo para a duração do serviço em minutos
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     *  Get the attributes that should be cast.
     *  @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * Um serviço pode estar em vários agendamentos.
     */
    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class)->withTimestamps();
    }
}
