<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api','permission:appointments.view'])->only(['index','show']);
        $this->middleware(['auth:api','permission:appointments.create'])->only(['store']);
        $this->middleware(['auth:api','permission:appointments.update'])->only(['update']);
        $this->middleware(['auth:api','permission:appointments.cancel'])->only(['cancel']);
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable','string'],
            'status' => ['nullable','in:reserved,paid,attended,cancelled,no_show'],
            'veterinarian_id' => ['nullable','exists:veterinarians,id'],
            'client_id' => ['nullable','exists:clients,id'],
            'pet_id' => ['nullable','exists:pets,id'],
            'date' => ['nullable','date_format:Y-m-d'],
        ]);

        $q = Appointment::with(['client','pet','veterinarian'])
            ->orderByDesc('starts_at');

        if ($request->status) $q->where('status', $request->status);
        if ($request->veterinarian_id) $q->where('veterinarian_id', $request->veterinarian_id);
        if ($request->client_id) $q->where('client_id', $request->client_id);
        if ($request->pet_id) $q->where('pet_id', $request->pet_id);

        if ($request->date) {
            $q->whereDate('starts_at', $request->date);
        }

        if ($search = trim((string)$request->search)) {
            $q->where(function($w) use ($search) {
                $w->whereHas('client', fn($qq)=>$qq->where('full_name','like',"%$search%"))
                  ->orWhereHas('pet', fn($qq)=>$qq->where('name','like',"%$search%"))
                  ->orWhereHas('veterinarian', fn($qq)=>$qq->where('full_name','like',"%$search%"));
            });
        }

        $perPage = (int) ($request->per_page ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        return $q->paginate($perPage);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();

        // Validación: la mascota debe pertenecer al cliente
        $pet = Pet::where('id', $data['pet_id'])
            ->where('client_id', $data['client_id'])
            ->first();

        if (!$pet) {
            return response()->json([
                'message' => 'La mascota no pertenece al cliente.',
            ], 422);
        }

        return DB::transaction(function () use ($data) {
            $slot = AvailabilitySlot::lockForUpdate()->find($data['slot_id']);

            if (!$slot) {
                return response()->json(['message' => 'Slot no encontrado'], 404);
            }

            if ($slot->service_type !== 'appointment') {
                return response()->json(['message' => 'Este slot no es para citas.'], 422);
            }

            if ($slot->status !== 'available') {
                return response()->json(['message' => 'Este slot ya no está disponible.'], 422);
            }

            if ((int)$slot->veterinarian_id !== (int)$data['veterinarian_id']) {
                return response()->json(['message' => 'El slot no corresponde al veterinario seleccionado.'], 422);
            }

            // marcar slot como booked
            $slot->update(['status' => 'booked']);

            $appt = Appointment::create([
                'client_id' => $data['client_id'],
                'pet_id' => $data['pet_id'],
                'veterinarian_id' => $data['veterinarian_id'],
                'slot_id' => $slot->id,
                'starts_at' => $slot->starts_at,
                'ends_at' => $slot->ends_at,
                'status' => 'reserved',
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return response()->json($appt->load(['client','pet','veterinarian']), 201);
        });
    }

    public function show(Appointment $appointment)
    {
        return response()->json($appointment->load(['client','pet','veterinarian','slot']));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());
        return response()->json($appointment->refresh()->load(['client','pet','veterinarian']));
    }

    // POST /api/appointments/{appointment}/cancel
    public function cancel(CancelAppointmentRequest $request, Appointment $appointment)
    {
        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'Ya está cancelada.'], 422);
        }

        return DB::transaction(function () use ($appointment, $request) {
            $slot = AvailabilitySlot::lockForUpdate()->find($appointment->slot_id);

            // liberar slot si existe
            if ($slot && $slot->status === 'booked') {
                $slot->update(['status' => 'available']);
            }

            $appointment->update([
                'status' => 'cancelled',
                'notes' => $request->notes ?? $appointment->notes,
            ]);

            return response()->json(['message' => 'Cita cancelada.']);
        });
    }
}
