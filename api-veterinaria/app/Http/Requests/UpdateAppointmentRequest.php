<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['sometimes','required','in:reserved,paid,attended,cancelled,no_show'],
            'reason' => ['nullable','string'],
            'notes' => ['nullable','string'],
        ];
    }


}
