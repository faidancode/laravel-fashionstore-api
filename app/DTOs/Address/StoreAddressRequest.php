<?php

namespace App\DTOs\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:50', // Contoh: Rumah, Kantor
            'receiver' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'city_id' => 'required|integer',
            'full_address' => 'required|string',
            'is_primary' => 'boolean',
        ];
    }

    public function toDto(): array
    {
        return array_merge($this->validated(), [
            'user_id' => $this->user()->id, // Otomatis ambil dari auth
        ]);
    }
}