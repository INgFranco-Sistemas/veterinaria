<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id' => ['required','exists:clients,id'],
            'pet_id' => ['required','exists:pets,id'],
            'veterinarian_id' => ['required','exists:veterinarians,id'],
            'slot_id' => ['required','exists:availability_slots,id'],
            'reason' => ['nullable','string'],
            'notes' => ['nullable','string'],
        ];
    }
}