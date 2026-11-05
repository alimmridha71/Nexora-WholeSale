@extends('layouts.app', ['title' => 'Request a quote'])

@section('content')
    <h1 class="text-2xl font-bold">Request a custom quote</h1>
    <p class="mt-1 text-slate-600">For volumes beyond our standard tiers. Leave unused rows empty.</p>

    <form method="POST" action="{{ route('quotes.store') }}" class="card mt-6 space-y-6">
        @csrf

        @error('items') <p class="error">{{ $message }}</p> @enderror

        <div class="space-y-4">
            @for ($i = 0; $i < $rows; $i++)
                @php($defaultProduct = $i === 0 && $preselected ? $preselected : null)
                <fieldset class="grid gap-3 rounded-lg border border-slate-200 p-4 sm:grid-cols-12">
                    <legend class="px-1 text-sm font-medium text-slate-500">Item {{ $i + 1 }}</legend>

                    <div class="sm:col-span-6">
                        <label for="items-{{ $i }}-product" class="label">Product</label>
                        <select id="items-{{ $i }}-product" name="items[{{ $i }}][product_id]" class="input">
                            <option value="">— Select a product —</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((int) old("items.$i.product_id", $defaultProduct) === $product->id)>
                                    {{ $product->name }} ({{ $product->sku }}) — @money($product->base_price)
                                </option>
                            @endforeach
                        </select>
                        @error("items.$i.product_id") <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="items-{{ $i }}-quantity" class="label">Quantity</label>
                        <input id="items-{{ $i }}-quantity" name="items[{{ $i }}][quantity]" type="number" min="1" max="1000000" value="{{ old("items.$i.quantity") }}" class="input">
                        @error("items.$i.quantity") <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="items-{{ $i }}-notes" class="label">Notes</label>
                        <input id="items-{{ $i }}-notes" name="items[{{ $i }}][notes]" type="text" maxlength="500" value="{{ old("items.$i.notes") }}" class="input" placeholder="Packaging, colour, delivery…">
                        @error("items.$i.notes") <p class="error">{{ $message }}</p> @enderror
                    </div>
                </fieldset>
            @endfor
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="target_price" class="label">Target total price ({{ config('nexora.currency') }}, optional)</label>
                <input id="target_price" name="target_price" type="number" step="0.01" min="0" value="{{ old('target_price') }}" class="input">
                @error('target_price') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="message" class="label">Message (optional)</label>
                <textarea id="message" name="message" rows="3" maxlength="5000" class="input">{{ old('message') }}</textarea>
                @error('message') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('quotes.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Submit quote request</button>
        </div>
    </form>
@endsection
