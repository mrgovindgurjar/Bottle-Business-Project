@php
    $editing = isset($order);
    $source = $sourceQuotation ?? null;
    $formItems = old('items');
    if ($formItems === null && $editing) {
        $formItems = $order->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'design_id' => $item->design_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $item->unit_price,
            'discount_amount' => $item->discount_amount,
            'tax_rate' => $item->tax_rate,
        ])->values()->all();
    }
    if ($formItems === null && $source) {
        $formItems = $source->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'design_id' => $item->design_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $item->unit_price,
            'discount_amount' => $item->discount_amount,
            'tax_rate' => $item->tax_rate,
        ])->values()->all();
    }
    if (!$formItems) $formItems = [[ 'product_id'=>'', 'design_id'=>'', 'description'=>'', 'quantity'=>1, 'unit'=>'bottle', 'unit_price'=>0, 'discount_amount'=>0, 'tax_rate'=>0 ]];
    $selectedCustomer = old('customer_id', $editing ? $order->customer_id : ($source?->customer_id ?? ''));
    $selectedQuotation = old('quotation_id', $editing ? $order->quotation_id : ($source?->id ?? ''));
    $selectedDesign = old('design_id', $editing ? $order->design_id : ($source?->design_id ?? ''));
@endphp

<form method="POST" action="{{ $editing ? route('admin.orders.update', $order) : route('admin.orders.store') }}" class="ord-form" id="orderForm">
    @csrf
    @if($editing) @method('PUT') @endif

    <section class="ord-section">
        <div class="ord-section-head"><div><span>01</span><div><h3>Order Header</h3><p>Set the order date and production deadline.</p></div></div><span class="ord-badge ord-badge-{{ $editing ? $order->status : 'draft' }}">{{ ucfirst(str_replace('_',' ', $editing ? $order->status : 'draft')) }}</span></div>
        <div class="ord-grid ord-grid-4">
            <label class="ord-field"><span>Order Number</span><input value="{{ $editing ? $order->order_number : 'Auto generated' }}" readonly></label>
            <label class="ord-field"><span>Order Date *</span><input type="date" name="order_date" value="{{ old('order_date',$editing ? $order->order_date?->format('Y-m-d') : today()->format('Y-m-d')) }}" required></label>
            <label class="ord-field"><span>Required By</span><input type="date" name="required_date" value="{{ old('required_date',$editing ? $order->required_date?->format('Y-m-d') : '') }}"></label>
            <label class="ord-field"><span>Source Quotation</span><select name="quotation_id" id="ordQuotation"><option value="">Manual order</option>@foreach($quotations as $q)<option value="{{ $q->id }}" @selected((string)$selectedQuotation===(string)$q->id)>{{ $q->quotation_number }} · {{ $q->customer->business_name }} · ₹{{ number_format($q->grand_total,2) }}</option>@endforeach</select></label>
        </div>
        <div class="ord-source-note" id="ordSourceNote">{{ $source ? 'Loaded from '.$source->quotation_number.'. Prices are preserved as order snapshots.' : 'Tip: choose an approved quotation to load its customer, design and items.' }}</div>
    </section>

    <section class="ord-section">
        <div class="ord-section-head"><div><span>02</span><div><h3>Customer</h3><p>The business receiving this order.</p></div></div></div>
        <div class="ord-grid ord-grid-2">
            <label class="ord-field"><span>Customer *</span><select name="customer_id" id="ordCustomer" required><option value="">Select customer</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected((string)$selectedCustomer===(string)$c->id)>{{ $c->business_name }} · {{ $c->customer_code }}</option>@endforeach</select></label>
            <div class="ord-customer-card" id="ordCustomerCard"><div class="ord-avatar">C</div><div><strong>Select a customer</strong><span>Contact and delivery details will appear here.</span></div></div>
        </div>
    </section>

    <section class="ord-section">
        <div class="ord-section-head"><div><span>03</span><div><h3>Design</h3><p>Optional Design Studio reference for production.</p></div></div></div>
        <div class="ord-grid ord-grid-2">
            <label class="ord-field"><span>Design Project</span><select name="design_id" id="ordDesign"><option value="">No design selected</option>@foreach($designs as $d)<option value="{{ $d->id }}" @selected((string)$selectedDesign===(string)$d->id)>{{ $d->design_code }} · {{ $d->title }} · {{ ucfirst($d->status) }}</option>@endforeach</select></label>
            <div class="ord-design-card" id="ordDesignCard"><strong>{{ $selectedDesign ? 'Design selected' : 'No design selected' }}</strong><span>Approved artwork is recommended before production.</span></div>
        </div>
    </section>

    <section class="ord-section">
        <div class="ord-section-head"><div><span>04</span><div><h3>Order Items</h3><p>Unit prices are stored on the order so future pricing changes never alter history.</p></div></div><button type="button" class="ord-btn ord-btn-light" data-ord-add-item>+ Add Item</button></div>
        <div class="ord-table-wrap"><table class="ord-items"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Discount</th><th>Tax %</th><th>Total</th><th></th></tr></thead><tbody id="ordItemsBody"></tbody></table></div>
    </section>

    <section class="ord-section ord-pricing-section">
        <div class="ord-section-head"><div><span>05</span><div><h3>Charges & Total</h3><p>Shipping and other charges are included in the server-side total.</p></div></div></div>
        <div class="ord-pricing-grid">
            <div class="ord-grid ord-grid-2">
                <label class="ord-field"><span>Shipping / Delivery</span><input type="number" min="0" step="0.01" name="shipping_amount" id="ordShipping" value="{{ old('shipping_amount',$editing?$order->shipping_amount:($source?->shipping_amount ?? 0)) }}"></label>
                <label class="ord-field"><span>Other Charges</span><input type="number" min="0" step="0.01" name="other_amount" id="ordOther" value="{{ old('other_amount',$editing?$order->other_amount:($source?->other_amount ?? 0)) }}"></label>
            </div>
            <aside class="ord-total-card"><div><span>Subtotal</span><strong id="ordSubtotal">₹0.00</strong></div><div><span>Discount</span><strong id="ordDiscount">− ₹0.00</strong></div><div><span>Tax</span><strong id="ordTax">₹0.00</strong></div><div><span>Shipping + Other</span><strong id="ordCharges">₹0.00</strong></div><div class="ord-grand"><span>Grand Total</span><strong id="ordGrand">₹0.00</strong></div></aside>
        </div>
    </section>

    <section class="ord-section">
        <div class="ord-section-head"><div><span>06</span><div><h3>Delivery & Notes</h3><p>Capture the address snapshot and operational instructions.</p></div></div></div>
        <div class="ord-grid ord-grid-4">
            <label class="ord-field ord-span-2"><span>Delivery Address</span><textarea name="delivery_address" rows="3" id="ordAddress">{{ old('delivery_address',$editing?$order->delivery_address:($source?->customer?->address ?? '')) }}</textarea></label>
            <label class="ord-field"><span>City</span><input name="delivery_city" id="ordCity" value="{{ old('delivery_city',$editing?$order->delivery_city:($source?->customer?->city ?? '')) }}"></label>
            <label class="ord-field"><span>Pincode</span><input name="delivery_pincode" id="ordPincode" value="{{ old('delivery_pincode',$editing?$order->delivery_pincode:($source?->customer?->pincode ?? '')) }}"></label>
            <label class="ord-field"><span>State</span><input name="delivery_state" id="ordState" value="{{ old('delivery_state',$editing?$order->delivery_state:($source?->customer?->state ?? '')) }}"></label>
            <label class="ord-field ord-span-3"><span>Customer Notes</span><textarea name="notes" rows="3">{{ old('notes',$editing?$order->notes:($source?->notes ?? '')) }}</textarea></label>
            <label class="ord-field ord-span-4"><span>Internal Notes</span><textarea name="internal_notes" rows="3">{{ old('internal_notes',$editing?$order->internal_notes:'') }}</textarea></label>
        </div>
    </section>

    <div class="ord-form-actions"><a class="ord-btn ord-btn-light" href="{{ route('admin.orders.index') }}">Cancel</a><div><button type="submit" class="ord-btn ord-btn-dark">{{ $editing ? 'Save Changes' : 'Save Draft' }}</button><button type="button" class="ord-btn ord-btn-primary" data-ord-preview>Preview</button></div></div>
</form>

<script>
window.JALVAN_ORDER = {!! \Illuminate\Support\Js::from([
    'editing' => $editing,
    'order' => $editing ? $order->only(['id','order_number','customer_id','quotation_id','design_id','shipping_amount','other_amount']) : null,
    'sourceQuotation' => $source?->only(['id','quotation_number','customer_id','design_id','shipping_amount','other_amount','notes']),
    'customers' => $customers->map(fn($c)=>[
        'id'=>$c->id,'business_name'=>$c->business_name,'customer_code'=>$c->customer_code,
        'mobile'=>$c->user?->mobile,'email'=>$c->user?->email,'address'=>$c->address,
        'city'=>$c->city,'state'=>$c->state,'pincode'=>$c->pincode,
    ])->values(),
    'products' => $products->map(fn($p)=>$p->only(['id','name','sku','bottle_size_ml','unit']))->values(),
    'items' => $formItems,
    'designs' => $designs->map(fn($d)=>[
        'id'=>$d->id,'code'=>$d->design_code,'title'=>$d->title,'status'=>$d->status,
    ])->values(),
    'routes' => [
        'price'=>route('admin.orders.price'),
        'designs'=>route('admin.orders.designs'),
        'quotationBase'=>url('/admin/order-data/quotations'),
        'preview'=>$editing ? route('admin.orders.preview',$order) : route('admin.orders.index'),
    ],
]) !!};
</script>
