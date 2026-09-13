(() => {
    'use strict';

    document.addEventListener('click', event => {
        if (event.target.closest('[data-ord-print]')) window.print();
    });

    const boot = window.JALVAN_ORDER;
    const body = document.getElementById('ordItemsBody');
    const form = document.getElementById('orderForm');
    if (!boot || !body || !form) return;

    const money = (n) => `₹${Number(n || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    const num = (value) => {
        const n = Number.parseFloat(value);
        return Number.isFinite(n) ? n : 0;
    };
    const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[ch]));

    let rows = Array.isArray(boot.items) && boot.items.length ? boot.items.map(item => ({...item})) : [];
    let priceTimer = null;

    const customerById = id => (boot.customers || []).find(c => String(c.id) === String(id));
    const productById = id => (boot.products || []).find(p => String(p.id) === String(id));

    function productOptions(selected) {
        return `<option value="">Select product</option>` + (boot.products || []).map(p =>
            `<option value="${p.id}" ${String(selected)===String(p.id)?'selected':''}>${esc(p.name)} · ${esc(p.sku)} · ${p.bottle_size_ml}ml</option>`
        ).join('');
    }

    function renderRows() {
        body.innerHTML = rows.map((item, index) => `
            <tr data-row="${index}">
                <td><select name="items[${index}][product_id]" data-field="product_id">${productOptions(item.product_id)}</select></td>
                <td><input name="items[${index}][description]" data-field="description" value="${esc(item.description)}" placeholder="Custom branded bottle"></td>
                <td><input class="ord-number-input" type="number" min="0.001" step="0.001" name="items[${index}][quantity]" data-field="quantity" value="${esc(item.quantity ?? 1)}"></td>
                <td><input class="ord-unit-input" name="items[${index}][unit]" data-field="unit" value="${esc(item.unit ?? 'bottle')}"></td>
                <td><input class="ord-number-input" type="number" min="0" step="0.01" name="items[${index}][unit_price]" data-field="unit_price" value="${esc(item.unit_price ?? 0)}"></td>
                <td><input class="ord-number-input" type="number" min="0" step="0.01" name="items[${index}][discount_amount]" data-field="discount_amount" value="${esc(item.discount_amount ?? 0)}"></td>
                <td><input class="ord-number-input" type="number" min="0" max="100" step="0.01" name="items[${index}][tax_rate]" data-field="tax_rate" value="${esc(item.tax_rate ?? 0)}"></td>
                <td class="ord-line-total" data-total>₹0.00</td>
                <td><div class="ord-row-actions"><button type="button" title="Duplicate" data-row-action="duplicate">＋</button><button type="button" title="Delete" data-row-action="delete">×</button></div></td>
            </tr>`).join('');
        recalculate();
    }

    function syncFromDom() {
        rows = [...body.querySelectorAll('tr[data-row]')].map(tr => {
            const get = field => tr.querySelector(`[data-field="${field}"]`)?.value ?? '';
            return {
                product_id: get('product_id'), description: get('description'), quantity: get('quantity'),
                unit: get('unit'), unit_price: get('unit_price'), discount_amount: get('discount_amount'),
                tax_rate: get('tax_rate')
            };
        });
    }

    function recalculate() {
        let subtotal = 0, discount = 0, tax = 0;
        [...body.querySelectorAll('tr[data-row]')].forEach(tr => {
            const qty = num(tr.querySelector('[data-field="quantity"]')?.value);
            const price = num(tr.querySelector('[data-field="unit_price"]')?.value);
            const disc = Math.min(qty * price, Math.max(0, num(tr.querySelector('[data-field="discount_amount"]')?.value)));
            const rate = Math.min(100, Math.max(0, num(tr.querySelector('[data-field="tax_rate"]')?.value)));
            const base = Math.round(qty * price * 100) / 100;
            const taxable = Math.max(0, Math.round((base - disc) * 100) / 100);
            const lineTax = Math.round(taxable * rate) / 100;
            const total = Math.round((taxable + lineTax) * 100) / 100;
            subtotal += base; discount += disc; tax += lineTax;
            const cell = tr.querySelector('[data-total]'); if (cell) cell.textContent = money(total);
        });
        const shipping = Math.max(0, num(document.getElementById('ordShipping')?.value));
        const other = Math.max(0, num(document.getElementById('ordOther')?.value));
        const grand = Math.max(0, Math.round((subtotal - discount + tax + shipping + other) * 100) / 100);
        const set = (id, value, negative=false) => { const el = document.getElementById(id); if (el) el.textContent = `${negative && value ? '− ' : ''}${money(value)}`; };
        set('ordSubtotal', subtotal); set('ordDiscount', discount, true); set('ordTax', tax); set('ordCharges', shipping + other); set('ordGrand', grand);
    }

    function showCustomer(id, fillAddress = true) {
        const c = customerById(id), card = document.getElementById('ordCustomerCard');
        if (!card) return;
        if (!c) { card.innerHTML = '<div class="ord-avatar">C</div><div><strong>Select a customer</strong><span>Contact and delivery details will appear here.</span></div>'; return; }
        card.innerHTML = `<div class="ord-avatar">${esc((c.business_name || 'C').charAt(0).toUpperCase())}</div><div><strong>${esc(c.business_name)}</strong><span>${esc(c.customer_code)} · ${esc(c.mobile || 'No mobile')} · ${esc(c.email || 'No email')}</span></div>`;
        if (fillAddress) {
            const set = (id, value) => { const el = document.getElementById(id); if (el && !el.value) el.value = value || ''; };
            set('ordAddress', c.address); set('ordCity', c.city); set('ordState', c.state); set('ordPincode', c.pincode);
        }
        loadDesigns(id);
    }

    async function loadDesigns(customerId) {
        const select = document.getElementById('ordDesign');
        if (!select || !customerId || !boot.routes?.designs) return;
        select.innerHTML = '<option value="">Loading designs…</option>';
        try {
            const url = new URL(boot.routes.designs, window.location.origin); url.searchParams.set('customer_id', customerId);
            const response = await fetch(url, {headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            if (!response.ok) throw new Error('Design lookup failed');
            const designs = await response.json();
            select.innerHTML = '<option value="">No design selected</option>' + designs.flatMap(d => (d.versions?.length ? d.versions.map(v => ({id:d.id,label:`${d.code} · ${d.title} · V${v.version_no} · ${v.status}`})) : [{id:d.id,label:`${d.code} · ${d.title} · ${d.status}`}])).map(d => `<option value="${d.id}">${esc(d.label)}</option>`).join('');
            if (boot.order?.design_id) select.value = String(boot.order.design_id);
        } catch (e) {
            console.error(e);
            select.innerHTML = '<option value="">Designs unavailable</option>';
        }
    }

    async function updatePrice(tr) {
        const productId = tr.querySelector('[data-field="product_id"]')?.value;
        const customerId = document.getElementById('ordCustomer')?.value;
        const quantity = num(tr.querySelector('[data-field="quantity"]')?.value) || 1;
        if (!productId || !boot.routes?.price) return;
        clearTimeout(priceTimer);
        priceTimer = setTimeout(async () => {
            try {
                const url = new URL(boot.routes.price, window.location.origin);
                url.searchParams.set('product_id', productId); url.searchParams.set('customer_id', customerId || ''); url.searchParams.set('quantity', quantity);
                const response = await fetch(url, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                if (!response.ok) throw new Error('Price lookup failed');
                const data = await response.json();
                const input = tr.querySelector('[data-field="unit_price"]');
                if (input && num(input.value) === 0 && num(data.price) > 0) input.value = Number(data.price).toFixed(2);
                recalculate();
            } catch (e) { console.error(e); }
        }, 120);
    }

    async function loadQuotation(id) {
        if (!id || !boot.routes?.quotationBase) return;
        const note = document.getElementById('ordSourceNote');
        if (note) note.textContent = 'Loading quotation…';
        try {
            const response = await fetch(`${boot.routes.quotationBase}/${id}`, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            if (!response.ok) throw new Error('Quotation load failed');
            const q = await response.json();
            const customer = document.getElementById('ordCustomer'); customer.value = q.customer.id;
            showCustomer(q.customer.id, false);
            document.getElementById('ordDesign').value = q.design_id || '';
            document.getElementById('ordAddress').value = q.customer.address || '';
            document.getElementById('ordCity').value = q.customer.city || '';
            document.getElementById('ordState').value = q.customer.state || '';
            document.getElementById('ordPincode').value = q.customer.pincode || '';
            document.getElementById('ordShipping').value = q.totals.shipping_amount || 0;
            document.getElementById('ordOther').value = q.totals.other_amount || 0;
            rows = q.items || [];
            renderRows();
            if (note) note.textContent = `Loaded from ${q.number}. Approved quotation pricing has been copied into this order.`;
        } catch (e) {
            console.error(e); if (note) note.textContent = 'Could not load the quotation. Please try again.';
        }
    }

    function previewCurrent() {
        syncFromDom();
        const customer = customerById(document.getElementById('ordCustomer')?.value);
        let lines = '', subtotal = 0, discount = 0, tax = 0;
        rows.forEach((item, i) => {
            const qty=num(item.quantity), price=num(item.unit_price), base=Math.round(qty*price*100)/100, disc=Math.min(base,num(item.discount_amount)), taxable=Math.max(0,base-disc), t=Math.round(taxable*num(item.tax_rate)/100*100)/100, total=taxable+t;
            subtotal+=base; discount+=disc; tax+=t;
            lines += `<tr><td>${i+1}</td><td><strong>${esc(productById(item.product_id)?.name || 'Custom item')}</strong><br>${esc(item.description)}</td><td>${qty} ${esc(item.unit)}</td><td>₹${price.toFixed(2)}</td><td>₹${disc.toFixed(2)}</td><td>₹${t.toFixed(2)}</td><td><strong>₹${total.toFixed(2)}</strong></td></tr>`;
        });
        const shipping=num(document.getElementById('ordShipping')?.value), other=num(document.getElementById('ordOther')?.value), grand=Math.max(0,subtotal-discount+tax+shipping+other);
        const html=`<!doctype html><html><head><title>Order Preview</title><style>body{font-family:Arial,sans-serif;color:#172033;padding:35px}header{display:flex;justify-content:space-between;border-bottom:2px solid #172033;padding-bottom:18px}h1{margin:0}.muted{color:#69758a}.meta{display:flex;gap:35px;margin:25px 0}.meta>div{flex:1}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #e5e8ee;text-align:left}th{font-size:11px;text-transform:uppercase;background:#f5f7fa}.totals{width:330px;margin:20px 0 0 auto}.totals div{display:flex;justify-content:space-between;padding:7px}.grand{font-size:18px;font-weight:700;border-top:2px solid #172033;margin-top:5px;padding-top:12px}@media print{.no-print{display:none}}</style></head><body><header><div><h1>JALVAN</h1><div class="muted">Custom Branded Water Bottles</div></div><div><strong>ORDER</strong><br>Draft Preview</div></header><div class="meta"><div><div class="muted">CUSTOMER</div><strong>${esc(customer?.business_name || '—')}</strong><br>${esc(customer?.customer_code || '')}<br>${esc(customer?.mobile || '')}</div><div><div class="muted">DELIVERY</div>${esc(document.getElementById('ordAddress')?.value || '—')}<br>${esc([document.getElementById('ordCity')?.value,document.getElementById('ordState')?.value,document.getElementById('ordPincode')?.value].filter(Boolean).join(', '))}</div></div><table><thead><tr><th>#</th><th>Product / Description</th><th>Qty</th><th>Price</th><th>Discount</th><th>Tax</th><th>Total</th></tr></thead><tbody>${lines}</tbody></table><div class="totals"><div><span>Subtotal</span><strong>₹${subtotal.toFixed(2)}</strong></div><div><span>Discount</span><strong>− ₹${discount.toFixed(2)}</strong></div><div><span>Tax</span><strong>₹${tax.toFixed(2)}</strong></div><div><span>Shipping + Other</span><strong>₹${(shipping+other).toFixed(2)}</strong></div><div class="grand"><span>Grand Total</span><strong>₹${grand.toFixed(2)}</strong></div></div><p class="muted">Draft preview · Save the order to create the official order number.</p><script>window.onload=()=>window.print()<\/script></body></html>`;
        const win = window.open('', '_blank');
        if (win) { win.document.write(html); win.document.close(); }
    }

    body.addEventListener('click', e => {
        const button = e.target.closest('[data-row-action]'); if (!button) return;
        syncFromDom();
        const index = Number(button.closest('tr')?.dataset.row);
        if (button.dataset.rowAction === 'delete') {
            if (rows.length <= 1) return alert('At least one item is required.');
            rows.splice(index, 1);
        } else if (button.dataset.rowAction === 'duplicate') {
            rows.splice(index + 1, 0, {...rows[index]});
        }
        renderRows();
    });

    body.addEventListener('input', e => { if (e.target.matches('[data-field]')) recalculate(); });
    body.addEventListener('change', e => { if (e.target.matches('[data-field]')) { syncFromDom(); if (e.target.dataset.field === 'product_id' || e.target.dataset.field === 'quantity') updatePrice(e.target.closest('tr')); recalculate(); } });
    document.querySelector('[data-ord-add-item]')?.addEventListener('click', () => { syncFromDom(); rows.push({product_id:'',description:'',quantity:1,unit:'bottle',unit_price:0,discount_amount:0,tax_rate:0}); renderRows(); });
    document.getElementById('ordCustomer')?.addEventListener('change', e => showCustomer(e.target.value));
    document.getElementById('ordQuotation')?.addEventListener('change', e => { if (e.target.value) loadQuotation(e.target.value); });
    document.getElementById('ordShipping')?.addEventListener('input', recalculate);
    document.getElementById('ordOther')?.addEventListener('input', recalculate);
    document.querySelector('[data-ord-preview]')?.addEventListener('click', previewCurrent);

    document.addEventListener('DOMContentLoaded', () => {
        renderRows();
        const customerId = document.getElementById('ordCustomer')?.value;
        if (customerId) showCustomer(customerId, false);
        const quotationId = document.getElementById('ordQuotation')?.value;
        if (quotationId && !boot.editing && !boot.sourceQuotation) loadQuotation(quotationId);
    });
})();
