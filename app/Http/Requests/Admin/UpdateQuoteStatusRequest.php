<?php

namespace App\Http\Requests\Admin;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuoteStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quote = $this->route('quote');

        return $quote instanceof Quote && (bool) $this->user()?->can('update', $quote);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Quote $quote */
        $quote = $this->route('quote');

        return [
            'status' => [
                'required',
                Rule::enum(QuoteStatus::class)->only($quote->status->allowedTransitions()),
            ],
        ];
    }

    public function status(): QuoteStatus
    {
        return QuoteStatus::from($this->validated('status'));
    }
}
