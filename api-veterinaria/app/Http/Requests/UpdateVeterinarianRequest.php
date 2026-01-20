<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Si la ruta usa model binding, puede ser modelo u ID
        $vet = $this->route('veterinarian');
        $vetId = is_object($vet) ? $vet->id : $vet;

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'document_type' => ['nullable', 'string', 'max:10'],

            // ✅ unique correcto en update
            'document_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('veterinarians', 'document_number')->ignore($vetId),
            ],

            'full_name' => ['sometimes', 'required', 'string', 'max:255'],

            // ✅ unique correcto en update
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('veterinarians', 'email')->ignore($vetId),
            ],

            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'attention_area' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ];
    }
}
