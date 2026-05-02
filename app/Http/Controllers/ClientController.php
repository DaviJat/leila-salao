<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    // Gera o código OTP e "envia" via WhatsApp para clientes não logados.
    public function sendOtp(Request $request)
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

        // Gera um código de 6 dígitos
        $otp = rand(100000, 999999);

        $client->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10) // Código vale por 10 minutos
        ]);

        // Aqui entrará a integração real com a API do WhatsApp
        Log::info("WhatsApp para {$client->phone}: Seu código de acesso Cabeleila é {$otp}");

        return response()->json(['success' => true]);
    }

    // Encerra a sessão do cliente no site.
    public function logout(Request $request)
    {
        Auth::guard('clients')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sessão encerrada com sucesso.');
    }
}
