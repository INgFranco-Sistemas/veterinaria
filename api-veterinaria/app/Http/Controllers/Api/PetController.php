<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:pets.view'])->only(['index', 'show']);
        $this->middleware(['auth:api', 'permission:pets.create'])->only(['store']);
        $this->middleware(['auth:api', 'permission:pets.update'])->only(['update']);
        $this->middleware(['auth:api', 'permission:pets.delete'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = Pet::with('client');

        // filtro por cliente
        if ($clientId = $request->query('client_id')) {
            $q->where('client_id', $clientId);
        }

        // búsqueda
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('species', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%");
            });
        }

        if (!is_null($request->query('active'))) {
            $active = filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($active)) {
                $q->where('active', $active);
            }
        }

        $perPage = (int) $request->query('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        return $q->orderByDesc('id')->paginate($perPage);
    }

    public function store(StorePetRequest $request)
    {
        $pet = Pet::create($request->validated());
        return response()->json($pet, 201);
    }

    public function show(Pet $pet)
    {
        return response()->json($pet->load('client'));
    }

    public function update(UpdatePetRequest $request, Pet $pet)
    {
        $pet->update($request->validated());
        return response()->json($pet->refresh());
    }

    public function destroy(Pet $pet)
    {
        $pet->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
