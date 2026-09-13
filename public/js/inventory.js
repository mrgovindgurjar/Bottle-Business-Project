document.addEventListener('DOMContentLoaded', () => {
    const product = document.getElementById('invProduct');
    if (!product) return;
    product.addEventListener('change', () => {
        const option = product.options[product.selectedIndex];
        if (!option.value) return;
        const set = (id, value) => { const el = document.getElementById(id); if (el && !el.value) el.value = value || ''; };
        set('invName', option.dataset.name);
        set('invSku', option.dataset.sku);
        set('invUnit', option.dataset.unit || 'bottle');
    });
});
