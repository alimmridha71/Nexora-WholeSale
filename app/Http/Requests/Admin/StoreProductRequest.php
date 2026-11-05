<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Product::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['sku' => strtoupper(trim((string) $this->input('sku')))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9\-_.]*$/', 'unique:products,sku'],
            'description' => ['nullable', 'string', 'max:10000'],
            'base_price' => ['required', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'image' => config('nexora.uploads_enabled')
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000']
                : ['prohibited'],
        ];
    }
}
