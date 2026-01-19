<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes','required','exists:clients,id'],
            'name' => ['sometimes','required','string','max:255'],
            'species' => ['sometimes','required','string','max:50'],
            'breed' => ['nullable','string','max:255'],
            'sex' => ['nullable','string','max:10'],
            'birth_date' => ['nullable','date'],
            'weight_kg' => ['nullable','numeric','min:0','max:9999.99'],
            'notes' => ['nullable','string'],
            'active' => ['boolean'],
        ];
    }
}
