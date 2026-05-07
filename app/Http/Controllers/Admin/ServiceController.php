<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of services with pagination and sorting.
     */
    public function index(Request $request)
    {
        $filters = $this->filtersFromRequest($request);
        $query = $this->baseQuery($filters);

        $services = $query->paginate($filters['per_page'])
            ->withQueryString();

        return inertia('Admin/Services', [
            'services' => $services,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $data = $this->validatedServiceData($request);
        $data = $this->sanitizeServiceData($data);

        Service::create($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço criado com sucesso!');
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, int $id)
    {
        $service = Service::findOrFail($id);

        $data = $this->validatedServiceData($request, $id);
        $data = $this->sanitizeServiceData($data);

        $service->update($data);

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço atualizado com sucesso!');
    }

    /**
     * Delete the specified service.
     */
    public function destroy(int $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Serviço deletado com sucesso!');
    }

    /**
     * Build the base query with filters.
     */
    private function baseQuery(array $filters)
    {
        $query = Service::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $sortField = in_array($filters['sort_field'], ['name', 'price', 'duration_minutes'])
            ? $filters['sort_field']
            : 'name';

        $query->orderBy($sortField, $filters['sort_order'] === 1 ? 'asc' : 'desc');

        return $query;
    }

    /**
     * Extract and validate filters from request.
     */
    private function filtersFromRequest(Request $request)
    {
        return [
            'search' => $request->input('search', ''),
            'per_page' => in_array($request->input('per_page', 10), [10, 20, 50])
                ? $request->input('per_page')
                : 10,
            'sort_field' => $request->input('sort_field', 'name'),
            'sort_order' => in_array($request->input('sort_order'), [1, -1])
                ? $request->input('sort_order')
                : 1,
        ];
    }

    /**
     * Validate service data for creation or update.
     */
    private function validatedServiceData(Request $request, ?int $serviceId = null)
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')->ignore($serviceId),
            ],
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:9999.99',
            'duration_minutes' => 'required|integer|min:5|max:480',
        ], [
            'name.required' => 'O nome do serviço é obrigatório.',
            'name.unique' => 'Já existe um serviço com este nome.',
            'price.required' => 'O preço é obrigatório.',
            'price.numeric' => 'O preço deve ser um número válido.',
            'duration_minutes.required' => 'A duração é obrigatória.',
            'duration_minutes.min' => 'A duração mínima é 5 minutos.',
        ]);
    }

    /**
     * Sanitize service data before storage.
     */
    private function sanitizeServiceData(array $data)
    {
        return array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
    }
}
