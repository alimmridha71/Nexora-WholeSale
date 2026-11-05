<?php

namespace App\Http\Controllers;

use App\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('dashboard.admin', [
                'productCount' => Product::count(),
                'lowStockCount' => Product::where('stock', '<', 100)->count(),
                'openQuoteCount' => Quote::whereIn('status', [QuoteStatus::Pending->value, QuoteStatus::UnderReview->value])->count(),
                'recentQuotes' => Quote::with('user:id,name,email')->latest()->limit(5)->get(),
            ]);
        }

        return view('dashboard.buyer', [
            'recentQuotes' => Quote::ownedBy($user)->latest()->limit(5)->get(),
        ]);
    }
}
