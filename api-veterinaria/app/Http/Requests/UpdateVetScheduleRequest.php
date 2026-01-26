<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVetScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'start_time' => ['sometimes','required','date_format:H:i'],
            'end_time' => ['sometimes','required','date_format:H:i'],
            'slot_minutes' => ['sometimes','required','integer','in:15,20,30,45,60'],
            'active' => ['boolean'],
        ];
    }
}
