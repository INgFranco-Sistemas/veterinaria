<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVetScheduleRequest;
use App\Http\Requests\UpdateVetScheduleRequest;
use App\Models\Veterinarian;
use App\Models\VetSchedule;
use Illuminate\Http\Request;

class VetScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api','permission:schedules.view'])->only(['index','show']);
        $this->middleware(['auth:api','permission:schedules.create'])->only(['store']);
        $this->middleware(['auth:api','permission:schedules.update'])->only(['update']);
        $this->middleware(['auth:api','permission:schedules.delete'])->only(['destroy']);
    }

    // GET /api/veterinarians/{veterinarian}/schedules
    public function index(Veterinarian $veterinarian)
    {
        return response()->json(
            VetSchedule::where('veterinarian_id', $veterinarian->id)
                ->orderBy('weekday')
                ->get()
        );
    }

    // POST /api/veterinarians/{veterinarian}/schedules
    public function store(StoreVetScheduleRequest $request, Veterinarian $veterinarian)
    {
        $data = $request->validated();
        $data['veterinarian_id'] = $veterinarian->id;

        // si existe para ese día, lo actualizamos
        $schedule = VetSchedule::updateOrCreate(
            ['veterinarian_id' => $veterinarian->id, 'weekday' => $data['weekday']],
            $data
        );

        return response()->json($schedule, 201);
    }

    // GET /api/veterinarians/{veterinarian}/schedules/{schedule}
    public function show(Veterinarian $veterinarian, VetSchedule $schedule)
    {
        abort_if($schedule->veterinarian_id !== $veterinarian->id, 404);
        return response()->json($schedule);
    }

    // PUT /api/veterinarians/{veterinarian}/schedules/{schedule}
    public function update(UpdateVetScheduleRequest $request, Veterinarian $veterinarian, VetSchedule $schedule)
    {
        abort_if($schedule->veterinarian_id !== $veterinarian->id, 404);
        $schedule->update($request->validated());
        return response()->json($schedule->refresh());
    }

    // DELETE /api/veterinarians/{veterinarian}/schedules/{schedule}
    public function destroy(Veterinarian $veterinarian, VetSchedule $schedule)
    {
        abort_if($schedule->veterinarian_id !== $veterinarian->id, 404);
        $schedule->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
