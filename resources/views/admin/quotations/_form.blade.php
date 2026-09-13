@php
    $editing = isset($quotation);
    $items = old('items', $editing ? $quotation->items->map(fn($i) => [
        'product_id'=>$i->product_id,'design_id'=>$i->design_id,'description'=>$i->description,'quantity'=>$i->quantity,
        'unit'=>$i->unit,'unit_price'=>$i->unit_price,'discount_type'=>$i->discount_type,'discount_value'=>$i->discount_value,'tax_rate'=>$i->tax_rate,
    ])->values()->all() : []);
    if (!$items) $items = [[ 'product_id'=>'','design_id'=>'','description'=>'','quantity'=>1,'unit'=>'bottle','unit_price'=>0,'discount_type'=>'','discount_value'=>0,'tax_rate'=>0 ]];
@endphp

<form method="POST" action="{{ $editing ? route('admin.quotations.update',$quotation) : route('admin.quotations.store') }}" class="qt-form" id="quotationForm">
    @csrf
    @if($editing) @method('PUT') @endif

    <section class="qt-section">
        <div class="qt-section-head"><div><span>01</span><div><h3>Quotation Header</h3><p>Set the proposal date and validity.</p></div></div><span class="qt-badge qt-badge-draft">{{ $editing ? ucfirst($quotation->status) : 'Draft' }}</span></div>
        <div class="qt-grid qt-grid-4">
            <label class="qt-field"><span>Quotation Number</span><input value="{{ $editing ? $quotation->quotation_number : 'Auto generated' }}" readonly></label>
            <label class="qt-field"><span>Quotation Date *</span><input type="date" name="quotation_date" value="{{ old('quotation_date',$editing ? $quotation->quotation_date?->format('Y-m-d') : today()->format('Y-m-d')) }}" required></label>
            <label class="qt-field"><span>Valid Until</span><input type="date" name="valid_until" value="{{ old('valid_until',$editing ? $quotation->valid_until?->format('Y-m-d') : '') }}"></label>
            <div class="qt-field qt-readonly-card"><span>Status</span><strong>Draft</strong><small>Saved changes remain editable.</small></div>
        </div>
    </section>

    <section class="qt-section">
        <div class="qt-section-head"><div><span>02</span><div><h3>Customer</h3><p>Choose the business receiving this quotation.</p></div></div></div>
        <div class="qt-grid qt-grid-2">
            <label class="qt-field"><span>Customer *</span><select name="customer_id" id="qtCustomer" required><option value="">Select customer</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id',$editing ? $quotation->customer_id : '')==$c->id)>{{ $c->business_name }} · {{ $c->customer_code }}</option>@endforeach</select></label>
            <div class="qt-customer-card" id="qtCustomerCard"><div class="qt-avatar">C</div><div><strong>Select a customer</strong><span>Business contact details will appear here.</span></div></div>
        </div>
    </section>

    <section class="qt-section">
        <div class="qt-section-head"><div><span>03</span><div><h3>Design</h3><p>Optional Design Studio reference for this quotation.</p></div></div></div>
        <div class="qt-grid qt-grid-2">
            <label class="qt-field"><span>Design Project</span><select name="design_id" id="qtDesign"><option value="">No design selected</option></select></label>
            <div class="qt-design-card" id="qtDesignCard"><strong>No design selected</strong><span>Select a customer to load their design projects.</span></div>
        </div>
    </section>

    <section class="qt-section">
        <div class="qt-section-head"><div><span>04</span><div><h3>Quotation Items</h3><p>Pricing is snapshotted into the quotation and will not change later.</p></div></div><button type="button" class="qt-btn qt-btn-light" data-qt-add-item>+ Add Item</button></div>
        <div class="qt-table-wrap"><table class="qt-items"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Discount</th><th>Tax %</th><th>Total</th><th></th></tr></thead><tbody id="qtItemsBody"></tbody></table></div>
    </section>

    <section class="qt-section qt-pricing-section">
        <div class="qt-section-head"><div><span>05</span><div><h3>Pricing</h3><p>Quotation-level discount, tax and additional charges.</p></div></div></div>
        <div class="qt-pricing-grid">
            <div class="qt-grid qt-grid-2">
                <label class="qt-field"><span>Discount Type</span><select name="discount_type" id="qtDiscountType"><option value="">None</option><option value="percentage" @selected(old('discount_type',$editing?$quotation->discount_type:'')==='percentage')>Percentage</option><option value="fixed" @selected(old('discount_type',$editing?$quotation->discount_type:'')==='fixed')>Fixed Amount</option></select></label>
                <label class="qt-field"><span>Discount Value</span><input type="number" min="0" step="0.01" name="discount_value" id="qtDiscountValue" value="{{ old('discount_value',$editing?$quotation->discount_value:0) }}"></label>
                <label class="qt-field"><span>Tax Type</span><select name="tax_type" id="qtTaxType"><option value="none" @selected(old('tax_type',$editing?$quotation->tax_type:'none')==='none')>No Tax</option><option value="percentage" @selected(old('tax_type',$editing?$quotation->tax_type:'')==='percentage')>Percentage</option></select></label>
                <label class="qt-field"><span>Tax Rate %</span><input type="number" min="0" max="100" step="0.01" name="tax_rate" id="qtTaxRate" value="{{ old('tax_rate',$editing?$quotation->tax_rate:0) }}"></label>
                <label class="qt-field"><span>Shipping / Delivery</span><input type="number" min="0" step="0.01" name="shipping_amount" id="qtShipping" value="{{ old('shipping_amount',$editing?$quotation->shipping_amount:0) }}"></label>
                <label class="qt-field"><span>Other Charges</span><input type="number" min="0" step="0.01" name="other_amount" id="qtOther" value="{{ old('other_amount',$editing?$quotation->other_amount:0) }}"></label>
            </div>
            <aside class="qt-total-card"><div><span>Subtotal</span><strong id="qtSubtotal">₹0.00</strong></div><div><span>Item Discounts</span><strong id="qtItemDiscount">− ₹0.00</strong></div><div><span>Quotation Discount</span><strong id="qtGlobalDiscount">− ₹0.00</strong></div><div><span>Tax</span><strong id="qtTax">₹0.00</strong></div><div><span>Shipping + Other</span><strong id="qtCharges">₹0.00</strong></div><div class="qt-grand"><span>Grand Total</span><strong id="qtGrand">₹0.00</strong></div></aside>
        </div>
    </section>

    <section class="qt-section">
        <div class="qt-section-head"><div><span>06</span><div><h3>Notes & Terms</h3><p>Keep customer-facing information clear.</p></div></div></div>
        <div class="qt-grid qt-grid-2"><label class="qt-field"><span>Notes</span><textarea name="notes" rows="5" placeholder="Optional customer notes...">{{ old('notes',$editing?$quotation->notes:'') }}</textarea></label><label class="qt-field"><span>Terms & Conditions</span><textarea name="terms_conditions" rows="5" placeholder="Quotation validity, payment terms, delivery notes...">{{ old('terms_conditions',$editing?$quotation->terms_conditions:'') }}</textarea></label></div>
    </section>

    <div class="qt-form-actions"><a class="qt-btn qt-btn-light" href="{{ route('admin.quotations.index') }}">Cancel</a><div><button type="submit" class="qt-btn qt-btn-dark">{{ $editing ? 'Save Changes' : 'Save Draft' }}</button><button type="button" class="qt-btn qt-btn-primary" data-qt-preview>Preview</button></div></div>
</form>

<script>
window.JALVAN_QUOTATION = {!! \Illuminate\Support\Js::from([
    'editing' => $editing,
    'quotation' => $editing ? $quotation->only(['id','quotation_number','customer_id','design_id','discount_type','discount_value','tax_type','tax_rate','shipping_amount','other_amount']) : null,
    'customers' => $customers->map(fn($c)=>$c->only(['id','business_name','customer_code','address','city','state','pincode']))->values(),
    'products' => $products->map(fn($p)=>$p->only(['id','name','sku','bottle_size_ml','unit']))->values(),
    'items' => $items,
    'routes' => ['price'=>route('admin.quotations.price'),'designs'=>route('admin.quotations.designs'),'index'=>route('admin.quotations.index')],
]) !!};
</script>
