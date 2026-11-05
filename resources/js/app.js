// Nexora Wholesale front-end behaviour.
// Kept dependency-free and CSP-friendly: no inline handlers in Blade.

// Confirm destructive actions: <form data-confirm="Delete this product?">
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (form instanceof HTMLFormElement && form.dataset.confirm) {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    }
});

// Live unit-price preview on the product page: <input data-tier-quantity>
// with tiers serialized in <script type="application/json" id="tiers">.
const quantityInput = document.querySelector('[data-tier-quantity]');
const tiersEl = document.getElementById('tiers');
const output = document.querySelector('[data-tier-output]');

if (quantityInput && tiersEl && output) {
    const { currency, base, tiers } = JSON.parse(tiersEl.textContent);
    const money = new Intl.NumberFormat(undefined, { style: 'currency', currency });

    const update = () => {
        const qty = Math.max(1, parseInt(quantityInput.value, 10) || 1);
        const tier = tiers
            .filter((t) => qty >= t.min && (t.max === null || qty <= t.max))
            .sort((a, b) => b.min - a.min)[0];
        const unit = tier ? tier.price : base;

        output.textContent = `${money.format(unit)} / unit · ${money.format(unit * qty)} total`;
    };

    quantityInput.addEventListener('input', update);
    update();
}
