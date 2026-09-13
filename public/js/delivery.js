(function(){
'use strict';
const root=document.getElementById('deliveryItemsBody');
const data=window.JALVAN_DELIVERY||{};
function orders(){return Array.isArray(data.orders)?data.orders:[]}
function batches(){return Array.isArray(data.batches)?data.batches:[]}
function rowTemplate(i, orderItems){
 const opts=orderItems.map(o=>`<option value="${o.id}" data-product="${o.product_id||''}" data-unit="${o.unit||'bottle'}" data-description="${String(o.description||'').replace(/"/g,'&quot;')}">${escapeHtml(o.description||'Item')} · ${o.quantity} ${escapeHtml(o.unit||'unit')}</option>`).join('');
 const batchOpts=batches().map(b=>`<option value="${b.id}" data-product="${b.product_id||''}">${escapeHtml(b.batch_number)} · ${escapeHtml(b.product?.name||'Product')} · Avail ${b.available_quantity}</option>`).join('');
 return `<tr class="dlv-item-row"><td><select class="js-order-item" name="items[${i}][order_item_id]" required><option value="">Select</option>${opts}</select></td><td><input type="hidden" class="js-product" name="items[${i}][product_id]" value=""><span class="js-product-label">Select item</span></td><td><select class="js-batch" name="items[${i}][batch_id]"><option value="">Select batch</option>${batchOpts}</select></td><td><input name="items[${i}][description]" placeholder="Bottle / branded water"></td><td><input class="js-qty" type="number" min="0.001" step="0.001" name="items[${i}][quantity]" value="1" required></td><td><input class="js-unit" name="items[${i}][unit]" value="bottle" required></td><td><button type="button" class="dlv-icon danger" data-remove-item>×</button></td></tr>`;
}
function escapeHtml(s){return String(s).replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]))}
function populateOrder(orderId){
 const order=orders().find(o=>String(o.id)===String(orderId));
 if(!order) return;
 const summary=document.getElementById('customerSummary');
 if(summary) summary.innerHTML=`<strong>${escapeHtml(order.customer?.business_name||'Customer')}</strong><span>${escapeHtml(order.customer?.customer_code||'')} · ${escapeHtml(order.customer?.user?.mobile||'No mobile')}</span>`;
 const addr=document.querySelector('[name="delivery_address"]'); if(addr&&(!addr.value||addr.dataset.autofill==='1')){addr.value=order.delivery_address||'';addr.dataset.autofill='1'}
 [['delivery_city','delivery_city'],['delivery_state','delivery_state'],['delivery_pincode','delivery_pincode']].forEach(([id,key])=>{const el=document.getElementById(id);if(el&&(!el.value||el.dataset.autofill==='1')){el.value=order[key]||'';el.dataset.autofill='1'}});
 if(root){root.innerHTML=(order.items||[]).map((o,i)=>rowTemplate(i,[o])).join(''); bindRows()}
}
function bindRows(){
 document.querySelectorAll('.js-order-item').forEach(sel=>sel.addEventListener('change',()=>{
  const tr=sel.closest('tr'), opt=sel.selectedOptions[0]; if(!opt)return;
  const product=opt.dataset.product||''; const p=tr.querySelector('.js-product'); if(p)p.value=product;
  const label=tr.querySelector('.js-product-label'); if(label){const order=orders().find(o=>String(o.id)===String(document.getElementById('deliveryOrder')?.value));const oi=order?.items?.find(x=>String(x.id)===String(sel.value));label.textContent=oi?.product?.name||'Product'}
  const desc=tr.querySelector('[name$="[description]"]'); if(desc&&!desc.value)desc.value=opt.dataset.description||'';
  const unit=tr.querySelector('.js-unit'); if(unit)unit.value=opt.dataset.unit||'bottle';
  filterBatches(tr,product);
 }));
 document.querySelectorAll('[data-remove-item]').forEach(btn=>btn.addEventListener('click',()=>{const rows=root?.querySelectorAll('.dlv-item-row')||[];if(rows.length<=1){alert('At least one delivery item is required.');return}btn.closest('tr')?.remove();renumber()}));
}
function filterBatches(tr,product){const sel=tr.querySelector('.js-batch');if(!sel)return;Array.from(sel.options).forEach((o,i)=>{if(i===0)return;o.hidden=product && String(o.dataset.product)!==String(product)});if(sel.selectedOptions[0]?.hidden)sel.value=''}
function renumber(){root?.querySelectorAll('.dlv-item-row').forEach((tr,i)=>tr.querySelectorAll('[name]').forEach(el=>{el.name=el.name.replace(/items\[\d+\]/,`items[${i}]`)}))}
const orderSel=document.getElementById('deliveryOrder'); if(orderSel)orderSel.addEventListener('change',()=>populateOrder(orderSel.value));
const add=document.querySelector('[data-add-item]'); if(add)add.addEventListener('click',()=>{if(!root)return;const order=orders().find(o=>String(o.id)===String(orderSel?.value));if(!order){alert('Select an order first.');return}root.insertAdjacentHTML('beforeend',rowTemplate(root.querySelectorAll('.dlv-item-row').length,order.items||[]));bindRows()});
bindRows();
['deliverModal','failModal'].forEach(id=>{const m=document.getElementById(id);if(!m)return;document.querySelectorAll(id==='deliverModal'?'[data-open-deliver]':'[data-open-fail]').forEach(b=>b.addEventListener('click',()=>m.classList.add('open')));m.querySelectorAll('[data-close-modal]').forEach(b=>b.addEventListener('click',()=>m.classList.remove('open')));m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')})});
})();
