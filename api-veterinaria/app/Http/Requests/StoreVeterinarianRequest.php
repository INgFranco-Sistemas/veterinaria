<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVeterinarianRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable','exists:users,id'],
            'document_type' => ['nullable','string','max:10'],
            'document_number' => ['nullable','string','max:20','unique:veterinarians,document_number'],
            'full_name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255','unique:veterinarians,email'],
            'phone' => ['nullable','string','max:30'],
            'specialty' => ['nullable','string','max:255'],
            'attention_area' => ['nullable','string','max:255'],
            'active' => ['boolean'],
        ];
    }
}
