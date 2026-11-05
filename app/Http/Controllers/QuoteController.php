<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Quote requests from business buyers (retailer / wholesaler / distributor).
 * Routes: auth + verified + role:retailer,wholesaler,distributor.
 * Every action also authorizes against QuotePolicy (defense in depth).
 */
class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        // Always scoped to the signed-in buyer.
        $quotes = Quote::ownedBy($request->user())
            ->latest()
            ->paginate(15);

        return view('quotes.index', compact('quotes'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Quote::class);

        return view('quotes.create', [
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'base_price']),
            'preselected' => (int) $request->query('product'),
            'rows' => min((int) config('nexora.quotes.form_rows'), (int) config('nexora.quotes.max_items')),
        ]);
    }

    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $this->authorize('create', Quote::class);

        // user_id comes from the relationship, never from input.
        $quote = $request->user()->quotes()->create([
            'product_details_json' => $request->productDetails(),
            'target_price' => $request->validated('target_price'),
            'message' => $request->validated('message'),
        ]);

        return redirect()
            ->route('quotes.show', $quote)
            ->with('status', "Quote {$quote->reference} submitted. We'll review it shortly.");
    }

    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);

        return view('quotes.show', [
            'quote' => $quote,
            'products' => $quote->lineItemProducts(),
        ]);
    }

    /**
     * Withdraw a quote that is still pending.
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);

        $reference = $quote->reference;
        $quote->delete();

        return redirect()->route('quotes.index')->with('status', "Quote {$reference} withdrawn.");
    }
}
