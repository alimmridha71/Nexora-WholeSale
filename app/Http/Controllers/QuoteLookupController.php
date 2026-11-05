<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteLookupRequest;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public quote status check (throttle:60,1 + per-reference limiter).
 *
 * Requires the random quote reference AND the e-mail of the submitting
 * account, matched with parameterised query-builder calls only. Any
 * mismatch returns one generic error, so the endpoint cannot be used to
 * enumerate quotes or e-mail addresses. Only status fields are disclosed.
 */
class QuoteLookupController extends Controller
{
    public function create(): View
    {
        return view('quotes.lookup');
    }

    public function show(QuoteLookupRequest $request): View|RedirectResponse
    {
        $email = $request->validated('email');

        $quote = Quote::query()
            ->where('reference', $request->validated('reference'))
            ->whereHas('user', fn ($q) => $q->where('email', $email))
            ->first(['id', 'reference', 'status', 'created_at', 'updated_at']);

        if ($quote === null) {
            return back()
                ->withInput($request->only('reference', 'email'))
                ->withErrors(['reference' => 'No quote matches that reference and e-mail address.']);
        }

        return view('quotes.lookup-result', compact('quote'));
    }
}
