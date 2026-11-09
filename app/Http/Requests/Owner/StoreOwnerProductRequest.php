<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isShopOwner();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
