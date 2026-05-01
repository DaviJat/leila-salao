<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'full_name',       // Campo para o nome completo do cliente
    'phone',           // Campo para o número de telefone do cliente
    'email',           // Campo para o email do cliente
    'cpf',             // Campo para o CPF do cliente
    'birth_date',      // Campo para a data de nascimento do cliente
    'postal_code',     // Campo para o CEP do endereço do cliente
    'street',          // Campo para o nome da rua do endereço do cliente
    'number',          // Campo para o número do endereço
    'complement',      // Campo para complemento do endereço
    'neighborhood',    // Campo para o bairro do cliente
    'city',            // Campo para a cidade do cliente
    'state',           // Campo para o estado (UF) do cliente
    'notes',           // Campo para anotações adicionais sobre o cliente
    'otp_code',        // Código temporário para login via WhatsApp
    'otp_expires_at'   // Validade do código
])]
#[Hidden(['remember_token', 'otp_code'])]
class Client extends Authenticatable
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, Notifiable;

    /**
     *  Get the attributes that should be cast.
     *  @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'otp_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    // O método getAuthPassword é necessário para que o Laravel saiba qual campo usar para autenticação, mesmo que neste caso não tenhamos uma senha tradicional.
    public function getAuthPassword()
    {
        return '';
    }

    // Definindo a relação entre Cliente e Appointment, onde um cliente pode ter muitos agendamentos. 
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
