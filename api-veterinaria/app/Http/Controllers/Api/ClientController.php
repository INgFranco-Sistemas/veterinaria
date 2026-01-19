<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:clients.view'])->only(['index', 'show']);
        $this->middleware(['auth:api', 'permission:clients.create'])->only(['store']);
        $this->middleware(['auth:api', 'permission:clients.update'])->only(['update']);
        $this->middleware(['auth:api', 'permission:clients.delete'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = Client::query();

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
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

    public function store(StoreClientRequest $request)
    {
        $client = Client::create($request->validated());
        return response()->json($client, 201);
    }

    public function show(Client $client)
    {
        // si quieres devolver también mascotas
        return response()->json($client->load('pets'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return response()->json($client->refresh());
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
