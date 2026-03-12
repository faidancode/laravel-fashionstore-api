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
        'label'           => 'required|string|max:60', 
        'recipient_name'  => 'required|string|max:120', 
        'recipient_phone' => 'required|string|max:30',
        'street'          => 'required|string|max:255', 
        'subdistrict'     => 'nullable|string|max:120',
        'district'        => 'nullable|string|max:120',
        'city'            => 'required|string|max:120', 
        'province'        => 'nullable|string|max:120',
        'postal_code'     => 'nullable|string|max:20',
        'is_primary'      => 'boolean',
    ];
}

    public function toDto(): array
    {
        return array_merge($this->validated(), [
            'user_id' => $this->user()->id, // Otomatis ambil dari auth
        ]);
    }
}