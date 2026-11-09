<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $shopId = $this->route('shop') instanceof \App\Models\Shop
            ? $this->route('shop')->id
            : $this->route('shop');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('shops', 'slug')->ignore($shopId)],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
