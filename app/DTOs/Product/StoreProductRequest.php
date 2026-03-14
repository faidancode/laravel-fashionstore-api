<?php

namespace App\DTOs\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|uuid|exists:categories,id',
            'brand_id' => 'required|uuid|exists:brands,id',
            'name' => 'required|string|max:200',
            'slug' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function toDto(): array
    {
        return $this->validated();
    }
}

