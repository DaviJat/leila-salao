<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $clients = $this->baseQuery($filters)->paginate($filters['per_page'])->withQueryString();

        return Inertia::render('Admin/Clients', [
            'clients' => $clients,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedClientData($request);

        Client::create($this->sanitizeClientData($data));

        return back()->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        $client = Client::findOrFail($id);

        $data = $this->validatedClientData($request, $client->id);

        $client->update($this->sanitizeClientData($data));

        return back()->with('success', 'Cadastro atualizado com sucesso!');
    }

    private function baseQuery(array $filters): Builder
    {
        return Client::query()
            ->withCount('appointments')
            ->when($filters['search'], function (Builder $query, string $search) {
                $query->where(function (Builder $innerQuery) use ($search) {
                    $innerQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                });
            })
            ->orderBy($filters['sort_field'], $filters['sort_order'] === 1 ? 'asc' : 'desc');
    }

    private function filtersFromRequest(Request $request): array
    {
        $allowedSortFields = ['full_name', 'phone', 'email', 'cpf', 'birth_date', 'city', 'appointments_count'];
        $sortField = $request->input('sort_field', 'full_name');

        return [
            'search' => $request->input('search'),
            'per_page' => $this->resolveRowsPerPage($request->integer('per_page', 10)),
            'sort_field' => in_array($sortField, $allowedSortFields, true) ? $sortField : 'full_name',
            'sort_order' => $request->integer('sort_order', 1) >= 0 ? 1 : -1,
        ];
    }

    private function resolveRowsPerPage(int $perPage): int
    {
        return in_array($perPage, [10, 20, 50], true) ? $perPage : 10;
    }

    private function validatedClientData(Request $request, ?int $clientId = null): array
    {
        return $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                $clientId ? Rule::unique('clients', 'phone')->ignore($clientId) : Rule::unique('clients', 'phone'),
            ],
            'email' => 'nullable|email|max:255',
            'cpf' => [
                'nullable',
                'string',
                $clientId ? Rule::unique('clients', 'cpf')->ignore($clientId) : Rule::unique('clients', 'cpf'),
            ],
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
    }

    private function sanitizeClientData(array $data): array
    {
        $data['phone'] = preg_replace('/\D/', '', $data['phone']);

        if (!empty($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
        }

        if (!empty($data['postal_code'])) {
            $data['postal_code'] = preg_replace('/\D/', '', $data['postal_code']);
        }

        return $data;
    }
}
