<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBulkPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product && (bool) $this->user()?->can('update', $product);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'min_quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'max_quantity' => ['nullable', 'integer', 'gte:min_quantity', 'max:2147483647'],
            'price_per_unit' => ['required', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
        ];
    }

    /**
     * Reject tiers that overlap an existing tier, so exactly one tier
     * can ever apply to a given quantity.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Product $product */
                $product = $this->route('product');
                $max = $this->filled('max_quantity') ? $this->integer('max_quantity') : null;

                $overlaps = $product->bulkPrices()
                    ->overlapping($this->integer('min_quantity'), $max)
                    ->exists();

                if ($overlaps) {
                    $validator->errors()->add('min_quantity', 'This quantity range overlaps an existing price tier.');
                }
            },
        ];
    }
}
