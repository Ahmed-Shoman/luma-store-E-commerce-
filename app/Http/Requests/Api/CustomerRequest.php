<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer') ? $this->route('customer')->id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'password' => $id ? 'nullable|string|min:6' : 'required|string|min:6',
            'phone' => 'nullable|string|max:20|unique:customers,phone,' . $id,
            'governorate' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}