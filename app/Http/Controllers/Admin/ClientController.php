<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $clients = Client::query()
            ->withCount('appointments')
            ->when($search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            })
            ->orderBy('full_name', 'asc')
            ->get();

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,phone',
            'email' => 'nullable|email|max:255',
            'cpf' => 'nullable|string|unique:clients,cpf',
            'birth_date' => 'nullable|date',
            'postal_code' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:50',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'notes' => 'nullable|string',
        ]);

        // Limpa os caracteres especiais antes de salvar
        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        if (!empty($data['cpf'])) $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
        if (!empty($data['postal_code'])) $data['postal_code'] = preg_replace('/\D/', '', $data['postal_code']);

        Client::create($data);

        return back()->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => ['required', 'string', Rule::unique('clients')->ignore($client->id)],
            'email' => 'nullable|email|max:255',
            'cpf' => ['nullable', 'string', Rule::unique('clients')->ignore($client->id)],
            'birth_date' => 'nullable|date',
            'postal_code' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:50',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'notes' => 'nullable|string',
        ]);

        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        if (!empty($data['cpf'])) $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
        if (!empty($data['postal_code'])) $data['postal_code'] = preg_replace('/\D/', '', $data['postal_code']);

        $client->update($data);

        return back()->with('success', 'Cadastro atualizado com sucesso!');
    }
}
