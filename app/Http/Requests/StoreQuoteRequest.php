<?php

namespace App\Http\Requests;

use App\Models\Quote;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Quote::class);
    }

    /**
     * Drop completely empty line items (the form renders spare rows).
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (is_array($items)) {
            $items = array_values(array_filter(
                $items,
                fn ($item) => is_array($item) && (filled($item['product_id'] ?? null) || filled($item['quantity'] ?? null))
            ));

            $this->merge(['items' => $items]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:'.config('nexora.quotes.max_items')],
            'items.*' => ['required', 'array:product_id,quantity,notes'],
            'items.*.product_id' => ['bail', 'required', 'integer', 'min:1', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'target_price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one product to your quote request.',
            'items.*.product_id.distinct' => 'Each product may only appear once.',
        ];
    }

    /**
     * Normalised payload stored in quotes.product_details_json.
     *
     * @return list<array{product_id: int, quantity: int, notes: string|null}>
     */
    public function productDetails(): array
    {
        return array_map(fn (array $item) => [
            'product_id' => (int) $item['product_id'],
            'quantity' => (int) $item['quantity'],
            'notes' => isset($item['notes']) && $item['notes'] !== '' ? (string) $item['notes'] : null,
        ], $this->validated('items'));
    }
}
