<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Veterinarian;
use App\Http\Requests\StoreVeterinarianRequest;
use App\Http\Requests\UpdateVeterinarianRequest;
use Illuminate\Http\Request;

class VeterinarianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'permission:vets.view'])->only(['index','show']);
        $this->middleware(['auth:api', 'permission:vets.create'])->only(['store']);
        $this->middleware(['auth:api', 'permission:vets.update'])->only(['update']);
        $this->middleware(['auth:api', 'permission:vets.delete'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = Veterinarian::query();

        if ($search = $request->string('search')->toString()) {
            $q->where(function ($w) use ($search) {
                $w->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('document_number', 'like', "%$search%");
            });
        }

        if (!is_null($request->active)) {
            $q->where('active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        return $q->orderBy('id','desc')->paginate((int) ($request->per_page ?? 10));
    }

    public function store(StoreVeterinarianRequest $request)
    {
        return response()->json(
            Veterinarian::create($request->validated()),
            201
        );
    }

    public function show(Veterinarian $veterinarian)
    {
        return $veterinarian;
    }

    public function update(UpdateVeterinarianRequest $request, Veterinarian $veterinarian)
    {
        $veterinarian->update($request->validated());
        return $veterinarian->refresh();
    }

    public function destroy(Veterinarian $veterinarian)
    {
        $veterinarian->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
