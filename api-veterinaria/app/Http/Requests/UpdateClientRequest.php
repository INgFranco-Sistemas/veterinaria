<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('client');

        return [
            'user_id' => ['nullable','exists:users,id'],
            'document_type' => ['nullable','string','max:10'],
            'document_number' => ['nullable','string','max:20',"unique:clients,document_number,$id"],
            'full_name' => ['sometimes','required','string','max:255'],
            'email' => ['nullable','email','max:255',"unique:clients,email,$id"],
            'phone' => ['nullable','string','max:30'],
            'address' => ['nullable','string','max:255'],
            'active' => ['boolean'],
        ];
    }
}