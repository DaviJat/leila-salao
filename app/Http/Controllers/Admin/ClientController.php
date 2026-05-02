<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    // Listagem de clientes com busca básica
    public function index(Request $request)
    {
        $search = $request->input('search');

        $clients = Client::when($search, function ($query, $search) {
            $query->where('full_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })->orderBy('full_name')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search'])
        ]);
    }

    // Atualização de dados do cliente
    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,phone,' . $id,
        ]);

        $client = Client::findOrFail($id);
        $client->update($request->only(['full_name', 'phone']));

        return back()->with('success', 'Cadastro do cliente atualizado!');
    }
}
