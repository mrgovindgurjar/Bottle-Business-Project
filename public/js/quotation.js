(() => {
    'use strict';

    const boot = window.JALVAN_QUOTATION;
    if (!boot) return;

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('quotationForm');
        const body = document.getElementById('qtItemsBody');
        if (!form || !body) return;

        const customer = document.getElementById('qtCustomer');
        const design = document.getElementById('qtDesign');
        const customerCard = document.getElementById('qtCustomerCard');
        const designCard = document.getElementById('qtDesignCard');
        let rowCounter = 0;

        const money = n => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 2 }).format(Number(n) || 0);
        const esc = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function productOptions(selected = '') {
            return '<option value="">Custom item</option>' + boot.products.map(p => `<option value="${p.id}" ${String(selected)===String(p.id)?'selected':''}>${esc(p.name)} · ${esc(p.sku)} · ${p.bottle_size_ml}ml</option>`).join('');
        }

        function addItem(item = {}) {
            const i = rowCounter++;
            const row = document.createElement('tr');
            row.dataset.index = i;
            row.innerHTML = `
                <td><select name="items[${i}][product_id]" class="qt-product">${productOptions(item.product_id)}</select></td>
                <td><input name="items[${i}][description]" class="qt-description" value="${esc(item.description || '')}" placeholder="Bottle / branding / service" required></td>
                <td><input type="number" min="0.001" step="0.001" name="items[${i}][quantity]" class="qt-qty" value="${item.quantity ?? 1}" required></td>
                <td><input name="items[${i}][unit]" class="qt-unit" value="${esc(item.unit || 'bottle')}" required></td>
                <td><input type="number" min="0" step="0.01" name="items[${i}][unit_price]" class="qt-price" value="${item.unit_price ?? 0}" required></td>
                <td><div class="qt-inline-discount"><select name="items[${i}][discount_type]" class="qt-item-discount-type"><option value="">None</option><option value="percentage" ${item.discount_type==='percentage'?'selected':''}>%</option><option value="fixed" ${item.discount_type==='fixed'?'selected':''}>₹</option></select><input type="number" min="0" step="0.01" name="items[${i}][discount_value]" class="qt-item-discount" value="${item.discount_value ?? 0}"></div></td>
                <td><input type="number" min="0" max="100" step="0.01" name="items[${i}][tax_rate]" class="qt-item-tax" value="${item.tax_rate ?? 0}"></td>
                <td><strong class="qt-line-total">₹0.00</strong></td>
                <td><button type="button" class="qt-icon-btn" title="Duplicate" data-duplicate-item>＋</button><button type="button" class="qt-icon-btn danger" title="Delete" data-delete-item>×</button></td>`;
            body.appendChild(row);
            bindRow(row);
            recalculate();
            return row;
        }

        function bindRow(row) {
            row.querySelectorAll('input,select').forEach(el => el.addEventListener('input', recalculate));
            row.querySelector('.qt-product').addEventListener('change', async () => {
                const productId = row.querySelector('.qt-product').value;
                const qty = row.querySelector('.qt-qty').value || 1;
                const description = row.querySelector('.qt-description');
                if (!productId) return;
                const product = boot.products.find(p => String(p.id) === String(productId));
                if (product && !description.value) description.value = `${product.name} · ${product.bottle_size_ml}ml`;
                try {
                    const url = new URL(boot.routes.price, window.location.origin);
                    url.searchParams.set('product_id', productId); url.searchParams.set('customer_id', customer.value || ''); url.searchParams.set('quantity', qty);
                    const r = await fetch(url, {headers:{'Accept':'application/json'}}); const data = await r.json();
                    if (data.price !== undefined && Number(row.querySelector('.qt-price').value) === 0) row.querySelector('.qt-price').value = data.price;
                    recalculate();
                } catch (e) { console.error('Quotation price lookup failed', e); }
            });
            row.querySelector('[data-delete-item]').addEventListener('click', () => { if (body.children.length > 1) { row.remove(); renumber(); recalculate(); } else { row.querySelectorAll('input').forEach(x => { if (!['unit','quantity'].includes(x.classList[0])) x.value=''; }); row.querySelector('.qt-qty').value=1; row.querySelector('.qt-price').value=0; recalculate(); } });
            row.querySelector('[data-duplicate-item]').addEventListener('click', () => { const data={}; row.querySelectorAll('[name]').forEach(el => { const key=el.className; if(key.includes('qt-product'))data.product_id=el.value; if(key.includes('qt-description'))data.description=el.value; if(key.includes('qt-qty'))data.quantity=el.value; if(key.includes('qt-unit'))data.unit=el.value; if(key.includes('qt-price'))data.unit_price=el.value; if(key.includes('qt-item-discount-type'))data.discount_type=el.value; if(key.includes('qt-item-discount'))data.discount_value=el.value; if(key.includes('qt-item-tax'))data.tax_rate=el.value; }); addItem(data); });
        }

        function renumber() {
            [...body.children].forEach((row, idx) => row.querySelectorAll('[name]').forEach(el => el.name = el.name.replace(/items\[\d+\]/, `items[${idx}]`)));
        }

        function line(row) {
            const qty = Number(row.querySelector('.qt-qty').value) || 0, price = Number(row.querySelector('.qt-price').value) || 0;
            const base = Math.round(qty * price * 100) / 100;
            const type = row.querySelector('.qt-item-discount-type').value, value = Number(row.querySelector('.qt-item-discount').value) || 0;
            const discount = type === 'percentage' ? Math.min(base, Math.round(base * Math.min(value,100) / 100 * 100) / 100) : Math.min(base, value);
            const net = Math.max(0, Math.round((base-discount)*100)/100), taxRate=Number(row.querySelector('.qt-item-tax').value)||0;
            const tax=Math.round(net*taxRate/100*100)/100, total=Math.round((net+tax)*100)/100;
            row.querySelector('.qt-line-total').textContent=money(total);
            return {base,discount,tax,total};
        }

        function recalculate() {
            let subtotal=0,itemDiscount=0,itemTax=0;
            body.querySelectorAll('tr').forEach(row => { const x=line(row); subtotal+=x.base; itemDiscount+=x.discount; itemTax+=x.tax; });
            subtotal=Math.round(subtotal*100)/100; itemDiscount=Math.round(itemDiscount*100)/100; itemTax=Math.round(itemTax*100)/100;
            const gt=document.getElementById('qtDiscountType').value, gv=Number(document.getElementById('qtDiscountValue').value)||0;
            const globalDiscount=gt==='percentage'?Math.min(subtotal-itemDiscount, Math.round((subtotal-itemDiscount)*Math.min(gv,100)/100*100)/100):Math.min(Math.max(0,subtotal-itemDiscount),gv);
            const taxable=Math.max(0,Math.round((subtotal-itemDiscount-globalDiscount)*100)/100);
            const taxType=document.getElementById('qtTaxType').value, taxRate=Number(document.getElementById('qtTaxRate').value)||0;
            const globalTax=taxType==='percentage'?Math.round(taxable*taxRate/100*100)/100:0;
            const shipping=Number(document.getElementById('qtShipping').value)||0, other=Number(document.getElementById('qtOther').value)||0;
            const grand=Math.round((taxable+itemTax+globalTax+shipping+other)*100)/100;
            document.getElementById('qtSubtotal').textContent=money(subtotal); document.getElementById('qtItemDiscount').textContent='− '+money(itemDiscount); document.getElementById('qtGlobalDiscount').textContent='− '+money(globalDiscount); document.getElementById('qtTax').textContent=money(itemTax+globalTax); document.getElementById('qtCharges').textContent=money(shipping+other); document.getElementById('qtGrand').textContent=money(grand);
        }

        function updateCustomerCard() {
            const c=boot.customers.find(x=>String(x.id)===String(customer.value));
            if(!c){customerCard.innerHTML='<div class="qt-avatar">C</div><div><strong>Select a customer</strong><span>Business contact details will appear here.</span></div>';return;}
            customerCard.innerHTML=`<div class="qt-avatar">${esc(c.business_name.charAt(0).toUpperCase())}</div><div><strong>${esc(c.business_name)}</strong><span>${esc(c.customer_code)} · ${esc(c.city||'')} ${esc(c.pincode||'')}</span></div>`;
            loadDesigns(c.id);
        }
        async function loadDesigns(customerId) {
            try { const u=new URL(boot.routes.designs,window.location.origin);u.searchParams.set('customer_id',customerId);const r=await fetch(u,{headers:{'Accept':'application/json'}});const list=await r.json();const selected=boot.quotation?.design_id||'';design.innerHTML='<option value="">No design selected</option>'+list.flatMap(d=>d.versions?.map(v=>({d,v}))||[]).map(({d,v})=>`<option value="${d.id}" data-version="${v.id}" ${String(selected)===String(d.id)?'selected':''}>${esc(d.code)} · V${v.version_no} · ${esc(d.title)} · ${esc(v.status)}</option>`).join('');const current=list.find(d=>String(d.id)===String(design.value));designCard.innerHTML=current?`<strong>${esc(current.code)} · ${esc(current.title)}</strong><span>${esc(current.status)}</span>`:'<strong>No design selected</strong><span>Optional Design Studio reference.</span>'; } catch(e){console.error('Quotation design lookup failed',e);}
        }
        customer.addEventListener('change', updateCustomerCard); design.addEventListener('change',()=>{const opt=design.selectedOptions[0];designCard.innerHTML=opt?.value?`<strong>${esc(opt.textContent)}</strong><span>Linked Design Studio project</span>`:'<strong>No design selected</strong><span>Optional Design Studio reference.</span>';});
        document.querySelector('[data-qt-add-item]').addEventListener('click',()=>addItem());
        ['qtDiscountType','qtDiscountValue','qtTaxType','qtTaxRate','qtShipping','qtOther'].forEach(id=>document.getElementById(id).addEventListener('input',recalculate));
        document.querySelector('[data-qt-preview]')?.addEventListener('click',()=>{ if(boot.editing){ window.location.href=`${window.location.origin}/admin/quotations/${boot.quotation.id}/preview`; } else { alert('Save the quotation first, then use Preview.'); }});
        [...(boot.items||[])].forEach(addItem); if(!(boot.items||[]).length) addItem();
        updateCustomerCard(); recalculate();
    });
})();
