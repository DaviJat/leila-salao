<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppService
{
    /**
     * Envia o código OTP via WhatsApp para login do cliente.
     */
    public function sendOtp(string $phone, string $name, string $otp): void
    {
        $cleanPhone = '55' . preg_replace('/\D/', '', $phone);
        $adminNumber = env('WHATSAPP_ADMIN_NUMBER', '(75) 99999-0000');

        $message = "✂️ *Cabeleila* \n\nOlá, {$name}! Seu código de acesso é: *{$otp}*\n\nEste código expira em 10 minutos.\n\n_Se precisar de ajuda ou quiser alterar algo urgente, fale diretamente com a gente: {$adminNumber}_";

        $this->dispatchMessage($cleanPhone, $message);
    }

    /**
     * Notifica o cliente sobre mudança de status (Confirmação ou Cancelamento)
     */
    public function sendStatusNotification($client, $appointment, string $status): void
    {
        $cleanPhone = '55' . preg_replace('/\D/', '', $client->phone);
        $date = Carbon::parse($appointment->availability->date)->format('d/m/Y');
        $time = substr($appointment->availability->hour, 0, 5);

        $message = "";

        if ($status === 'confirmed') {
            $message = "✅ *Agendamento Confirmado!*\n\nOlá, {$client->full_name}! Seu horário para o dia *{$date}* às *{$time}* foi confirmado pela nossa equipe. Te esperamos ansiosamente!";
        } elseif ($status === 'canceled') {
            $message = "❌ *Agendamento Cancelado*\n\nOlá, {$client->full_name}. Informamos que seu agendamento do dia *{$date}* às *{$time}* foi cancelado. Se desejar, acesse nosso site para reagendar uma nova data.";
        }

        if ($message) {
            $this->dispatchMessage($cleanPhone, $message);
        }
    }

    /**
     * Notifica o cliente quando o Admin (Leila) cria ou altera o agendamento por ele
     */
    public function sendAdminActionNotification($client, $appointment, bool $isNew = false): void
    {
        $cleanPhone = '55' . preg_replace('/\D/', '', $client->phone);
        $date = Carbon::parse($appointment->availability->date)->format('d/m/Y');
        $time = substr($appointment->availability->hour, 0, 5);

        // Pega os nomes de todos os serviços agendados e junta com vírgula
        $services = $appointment->services->pluck('name')->join(', ');

        $action = $isNew ? "foi agendado" : "foi remarcado";
        $intro = $isNew ? "🎉 *Novo Agendamento!*" : "🔄 *Agendamento Alterado*";

        $message = "{$intro}\n\nOlá, {$client->full_name}! Um horário {$action} para você no salão:\n\n📅 *Data:* {$date}\n⏰ *Hora:* {$time}\n✂️ *Serviços:* {$services}\n\nPara gerenciar seus horários, acesse nosso site.";

        $this->dispatchMessage($cleanPhone, $message);
    }

    /**
     * Método privado centralizado para disparar a requisição HTTP.
     * Assim não repetimos código em todas as funções acima.
     */
    private function dispatchMessage(string $phone, string $message): void
    {
        try {
            // Ajuste a URL e o payload de acordo com a API que você for usar (Z-API, Evolution, etc)
            $response = Http::withToken(env('WHATSAPP_API_TOKEN'))
                ->post(env('WHATSAPP_API_URL'), [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->failed()) {
                Log::error("Falha ao enviar WhatsApp para {$phone}: " . $response->body());
            }
        } catch (\Exception $e) {
            // O try/catch garante que se o WhatsApp estiver fora do ar, 
            // o site não vai travar nem dar erro 500 para a cliente.
            Log::error("Exceção na API do WhatsApp: " . $e->getMessage());
        }
    }
}
