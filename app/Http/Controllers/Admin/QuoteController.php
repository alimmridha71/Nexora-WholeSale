<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateQuoteStatusRequest;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Routes: auth + verified + role:admin. Every action also authorizes
 * against QuotePolicy (defense in depth).
 */
class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        $status = QuoteStatus::tryFrom((string) $request->query('status'));

        $quotes = Quote::query()
            ->with('user:id,name,email,role')
            ->when($status, fn ($q) => $q->status($status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.quotes.index', [
            'quotes' => $quotes,
            'status' => $status,
            'statuses' => QuoteStatus::cases(),
        ]);
    }

    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);

        $quote->load('user:id,name,email,role');

        return view('admin.quotes.show', [
            'quote' => $quote,
            'products' => $quote->lineItemProducts(),
        ]);
    }

    public function update(UpdateQuoteStatusRequest $request, Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        $quote->status = $request->status();
        $quote->save();

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('status', "Quote {$quote->reference} marked as {$quote->status->label()}.");
    }
}
