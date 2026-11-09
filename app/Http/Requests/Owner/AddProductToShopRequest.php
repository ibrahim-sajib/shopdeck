<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class AddProductToShopRequest extends FormRequest
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
        ];
    }
}
