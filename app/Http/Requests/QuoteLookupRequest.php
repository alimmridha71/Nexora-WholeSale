<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Public quote status lookup. Requires BOTH the random quote reference and
 * the e-mail of the account that submitted it. The route is rate limited
 * per IP and per reference (see AppServiceProvider "quote-lookup").
 */
class QuoteLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reference' => strtoupper(trim((string) $this->input('reference'))),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $prefix = preg_quote((string) config('nexora.quotes.reference_prefix'), '/');

        return [
            'reference' => ['required', 'string', 'max:20', "regex:/^{$prefix}-[0-9A-HJKMNP-TV-Z]{8}$/"],
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reference.regex' => 'Enter the reference exactly as shown on your quote, e.g. '.config('nexora.quotes.reference_prefix').'-7KD9QX2M.',
        ];
    }
}
