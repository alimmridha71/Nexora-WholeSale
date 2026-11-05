<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product && (bool) $this->user()?->can('update', $product);
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
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9][A-Z0-9\-_.]*$/', Rule::unique('products', 'sku')->ignore($product->id)],
            'description' => ['nullable', 'string', 'max:10000'],
            'base_price' => ['required', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
