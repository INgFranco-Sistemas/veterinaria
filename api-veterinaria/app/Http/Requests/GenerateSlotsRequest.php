<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSlotsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'service_type' => ['required','in:appointment,vaccine,surgery'],
            'start_date' => ['required','date_format:Y-m-d'],
            'end_date' => ['required','date_format:Y-m-d','after_or_equal:start_date'],
            'only_active_days' => ['sometimes','boolean'],
        ];
    }
}
