<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateSlotsRequest;
use App\Models\AvailabilitySlot;
use App\Models\VetSchedule;
use App\Models\Veterinarian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilitySlotController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api','permission:slots.view'])->only(['index']);
        $this->middleware(['auth:api','permission:slots.generate'])->only(['generate']);
        $this->middleware(['auth:api','permission:slots.delete'])->only(['deleteRange']);
    }

    // GET /api/slots?veterinarian_id=1&service_type=appointment&date=2026-01-26
    public function index(Request $request)
    {
        $request->validate([
            'veterinarian_id' => ['required','exists:veterinarians,id'],
            'service_type' => ['required','in:appointment,vaccine,surgery'],
            'date' => ['nullable','date_format:Y-m-d'],
            'status' => ['nullable','in:available,blocked,booked'],
        ]);

        $q = AvailabilitySlot::query()
            ->where('veterinarian_id', $request->veterinarian_id)
            ->where('service_type', $request->service_type)
            ->orderBy('starts_at');

        if ($request->date) {
            $start = Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $request->date)->endOfDay();
            $q->whereBetween('starts_at', [$start, $end]);
        }

        if ($request->status) {
            $q->where('status', $request->status);
        }

        return $q->paginate((int) ($request->per_page ?? 50));
    }

    // POST /api/veterinarians/{veterinarian}/slots/generate
    public function generate(GenerateSlotsRequest $request, Veterinarian $veterinarian)
    {
        $data = $request->validated();

        $serviceType = $data['service_type'];
        $startDate = Carbon::createFromFormat('Y-m-d', $data['start_date'])->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $data['end_date'])->endOfDay();
        $onlyActive = (bool)($data['only_active_days'] ?? true);

        $schedules = VetSchedule::where('veterinarian_id', $veterinarian->id)
            ->when($onlyActive, fn($q) => $q->where('active', true))
            ->get()
            ->keyBy('weekday');

        if ($schedules->isEmpty()) {
            return response()->json([
                'message' => 'Este veterinario no tiene horarios configurados.',
            ], 422);
        }

        $created = 0;
        $skipped = 0;

        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            // Carbon: dayOfWeekIso => 1 (Lun) .. 7 (Dom)
            $weekday = $cursor->dayOfWeekIso;

            if ($schedules->has($weekday)) {
                $sch = $schedules->get($weekday);

                $dayStart = Carbon::parse($cursor->format('Y-m-d').' '.$sch->start_time);
                $dayEnd = Carbon::parse($cursor->format('Y-m-d').' '.$sch->end_time);

                $slot = $dayStart->copy();
                while ($slot->addMinutes(0)->lt($dayEnd)) {
                    $slotStart = $slot->copy();
                    $slotEnd = $slot->copy()->addMinutes((int)$sch->slot_minutes);

                    if ($slotEnd->gt($dayEnd)) break;

                    $exists = AvailabilitySlot::where('veterinarian_id', $veterinarian->id)
                        ->where('service_type', $serviceType)
                        ->where('starts_at', $slotStart)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                    } else {
                        AvailabilitySlot::create([
                            'veterinarian_id' => $veterinarian->id,
                            'service_type' => $serviceType,
                            'starts_at' => $slotStart,
                            'ends_at' => $slotEnd,
                            'status' => 'available',
                        ]);
                        $created++;
                    }

                    $slot = $slotStart->addMinutes((int)$sch->slot_minutes);
                }
            }

            $cursor->addDay();
        }

        return response()->json([
            'message' => 'Slots generados.',
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    // DELETE /api/veterinarians/{veterinarian}/slots/delete-range
    public function deleteRange(Request $request, Veterinarian $veterinarian)
    {
        $request->validate([
            'service_type' => ['required','in:appointment,vaccine,surgery'],
            'start_date' => ['required','date_format:Y-m-d'],
            'end_date' => ['required','date_format:Y-m-d','after_or_equal:start_date'],
            'only_status' => ['nullable','in:available,blocked,booked'],
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

        $q = AvailabilitySlot::where('veterinarian_id', $veterinarian->id)
            ->where('service_type', $request->service_type)
            ->whereBetween('starts_at', [$start, $end]);

        if ($request->only_status) {
            $q->where('status', $request->only_status);
        }

        $deleted = $q->delete();

        return response()->json(['deleted' => $deleted]);
    }
}
