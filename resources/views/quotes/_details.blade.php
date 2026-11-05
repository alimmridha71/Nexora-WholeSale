{{-- Shared quote line-item table. Expects $quote and $products (keyed by id). --}}
<table class="table">
    <thead>
        <tr><th>Product</th><th class="text-right">Quantity</th><th class="text-right">List unit price</th><th class="text-right">List total</th><th>Notes</th></tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @php($listTotal = 0)
        @foreach ($quote->product_details_json as $item)
            @php($product = $products->get($item['product_id']))
            @php($unit = $product ? (float) $product->getPriceForQuantity((int) $item['quantity']) : null)
            @php($lineTotal = $unit !== null ? $unit * $item['quantity'] : null)
            @php($listTotal += $lineTotal ?? 0)
            <tr>
                <td>{{ $product ? $product->name.' ('.$product->sku.')' : 'Product #'.$item['product_id'].' (no longer listed)' }}</td>
                <td class="text-right">{{ number_format($item['quantity']) }}</td>
                <td class="text-right">@money($unit)</td>
                <td class="text-right">@money($lineTotal)</td>
                <td class="text-slate-600">{{ $item['notes'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="font-semibold">
            <td colspan="3">List total at current tier prices</td>
            <td class="text-right">@money($listTotal)</td>
            <td></td>
        </tr>
        <tr class="font-semibold text-brand-700">
            <td colspan="3">Customer target price</td>
            <td class="text-right">@money($quote->target_price)</td>
            <td></td>
        </tr>
    </tfoot>
</table>
