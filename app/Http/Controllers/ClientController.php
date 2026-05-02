<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Appointment;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClientController extends Controller
{
    // Exibe a página "Meus Agendamentos"
    public function myAppointments()
    {
        $loggedClient = Auth::guard('clients')->user();
        $appointments = [];

        // Se estiver logado, busca o histórico completo de agendamentos do cliente
        if ($loggedClient) {
            $appointments = Appointment::with(['availability', 'services'])
                ->where('client_id', $loggedClient->id)
                ->get()
                ->sortByDesc(function ($app) {
                    // Ordena pela data e hora (mais recentes/futuros primeiro)
                    return $app->availability->date->format('Y-m-d') . ' ' . $app->availability->hour;
                })
                ->values();
        }

        return Inertia::render('Appointment/MyAppointments', [
            'loggedClient' => $loggedClient,
            'appointments' => $appointments
        ]);
    }

    // Gera o código OTP e envia via WhatsApp (Usando injeção de dependência)
    public function sendOtp(Request $request, WhatsAppService $whatsAppService)
    {
        $request->validate([
            'whatsapp' => 'required|string',
            'name' => 'required|string'
        ]);

        // Busca o cliente pelo celular ou cria um rascunho com o nome
        $client = Client::firstOrCreate(
            ['phone' => $request->whatsapp],
            ['full_name' => $request->name]
        );

        // Gera um código de 6 dígitos (forçado para string)
        $otp = (string) rand(100000, 999999);

        $client->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10) // Código vale por 10 minutos
        ]);

        // Mantemos o log para facilitar testes locais/debug
        Log::info("WhatsApp para {$client->phone}: Seu código de acesso Cabeleila é {$otp}");

        // Dispara a mensagem usando o nosso Service
        // $whatsAppService->sendOtp($client->phone, $client->full_name, $otp);

        return response()->json(['success' => true]);
    }

    // Valida o código OTP e loga o cliente (usado exclusivamente na tela "Meus Agendamentos")
    public function loginViaOtp(Request $request)
    {
        $request->validate([
            'whatsapp' => 'required|string',
            'otp' => 'required|string',
            'name' => 'required|string',
        ]);

        $client = Client::where('phone', $request->whatsapp)
            ->where('otp_code', $request->otp)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$client) {
            return back()->withErrors(['otp' => 'Código inválido ou expirado. Tente novamente.']);
        }

        // Atualiza o nome (caso o cliente queira corrigir) e limpa o OTP
        $client->update([
            'full_name' => $request->name,
            'otp_code' => null,
        ]);

        // Realiza o login criando a sessão segura
        Auth::guard('clients')->login($client, true);

        return back()->with('success', 'Login realizado com sucesso!');
    }

    // Encerra a sessão do cliente no site
    public function logout(Request $request)
    {
        Auth::guard('clients')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sessão encerrada com sucesso.');
    }
}
