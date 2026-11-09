<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isShopOwner();
    }

    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0'],
            'name_override' => ['nullable', 'string', 'max:255'],
            'description_override' => ['nullable', 'string'],
            'thumbnail_override' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
