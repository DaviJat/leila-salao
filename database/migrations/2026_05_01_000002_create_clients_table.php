<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Informações Pessoais
            $table->string('full_name');
            $table->string('phone')->unique(); // Identificador principal para o login
            $table->string('email')->unique()->nullable();
            $table->string('cpf', 14)->unique()->nullable();
            $table->date('birth_date')->nullable();

            // Endereço Estruturado
            $table->string('postal_code', 9)->nullable();
            $table->string('street')->nullable();
            $table->string('number', 10)->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();

            // Autenticação via WhatsApp (OTP)
            // Mantemos o password nullable porque o login será via código
            $table->string('password')->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->rememberToken();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
