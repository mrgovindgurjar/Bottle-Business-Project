@php
    $editing = isset($delivery);
    $order = $delivery?->order ?? $selectedOrder ?? null;
    $items = old('items');
    if ($items === null) {
        $items = $editing ? $delivery?->items->map(fn($i)=>[
            'order_item_id'=>$i->order_item_id,'product_id'=>$i->product_id,'batch_id'=>$i->batch_id,
            'description'=>$i->description,'quantity'=>$i->quantity,'unit'=>$i->unit,'notes'=>$i->notes,
        ])->all() : ($order ? $order->items->map(fn($i)=>[
            'order_item_id'=>$i->id,'product_id'=>$i->product_id,'batch_id'=>'','description'=>$i->description,
            'quantity'=>$i->quantity,'unit'=>$i->unit,'notes'=>'',
        ])->all() : [[]]);
    }
@endphp
<div class="dlv-form">
    <section class="dlv-section">
        <div class="dlv-section-head"><div><span>01</span><div><h3>Delivery Header</h3><p>Choose the customer order and delivery schedule.</p></div></div></div>
        <div class="dlv-grid dlv-grid-4">
            <label class="dlv-field dlv-span-2"><span>Order *</span><select id="deliveryOrder" name="order_id" required @disabled($editing)><option value="">Select order</option>@foreach($orders as $o)<option value="{{ $o->id }}" @selected((string)old('order_id',$order?->id)===(string)$o->id)>{{ $o->order_number }} · {{ $o->customer->business_name }}</option>@endforeach</select></label>
          <label class="dlv-field">
    <span>Delivery Date *</span>
    <input type="date" name="delivery_date" value="{{ old('delivery_date', isset($delivery) && $delivery->delivery_date ? $delivery->delivery_date->format('Y-m-d') : today()->toDateString()) }}" required>
</label>

            <label class="dlv-field"><span>Scheduled Date</span><input type="date" name="scheduled_date"  value="{{ old('delivery_date', isset($delivery) && $delivery->scheduled_date ? $delivery->scheduled_date->format('Y-m-d') : '') }}"></label>
        </div>
    </section>

    <section class="dlv-section">
        <div class="dlv-section-head"><div><span>02</span><div><h3>Customer & Address</h3><p>Delivery destination is copied from the order and can be adjusted.</p></div></div></div>
        <div id="customerSummary" class="dlv-summary">@if($order)<strong>{{ $order->customer->business_name }}</strong><span>{{ $order->customer->customer_code }} · {{ $order->customer->user?->mobile ?? 'No mobile' }}</span>@else<span>Select an order to load customer details.</span>@endif</div>
        <div class="dlv-grid dlv-grid-4">
            <label class="dlv-field dlv-span-4"><span>Address</span><textarea name="delivery_address" rows="2">{{ old('delivery_address',$delivery?->delivery_address ?? $order?->delivery_address ?? '') }}</textarea></label>
            <label class="dlv-field"><span>City</span><input id="deliveryCity" name="delivery_city" value="{{ old('delivery_city',$delivery?->delivery_city ?? $order?->delivery_city ?? '') }}"></label>
            <label class="dlv-field"><span>State</span><input id="deliveryState" name="delivery_state" value="{{ old('delivery_state',$delivery?->delivery_state ?? $order?->delivery_state ?? '') }}"></label>
            <label class="dlv-field"><span>Pincode</span><input id="deliveryPincode" name="delivery_pincode" value="{{ old('delivery_pincode',$delivery?->delivery_pincode ?? $order?->delivery_pincode ?? '') }}"></label>
            <label class="dlv-field"><span>Assign To</span><select name="assigned_to"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected((string)old('assigned_to',$delivery?->assigned_to)===(string)$u->id)>{{ $u->name }}</option>@endforeach</select></label>
        </div>
    </section>

    <section class="dlv-section">
        <div class="dlv-section-head"><div><span>03</span><div><h3>Delivery Items</h3><p>Every dispatch item must have a batch for traceability.</p></div></div><button type="button" class="dlv-btn dlv-dark" data-add-item>+ Add Item</button></div>
        <div class="dlv-table-wrap"><table class="dlv-items"><thead><tr><th>Order Item</th><th>Product</th><th>Batch</th><th>Description</th><th>Qty</th><th>Unit</th><th></th></tr></thead><tbody id="deliveryItemsBody">
        @foreach($items as $idx=>$item)
            <tr class="dlv-item-row">
                <td><select class="js-order-item" name="items[{{ $idx }}][order_item_id]" required><option value="">Select</option>@foreach(($order?->items ?? []) as $oi)<option value="{{ $oi->id }}" data-product="{{ $oi->product_id }}" data-unit="{{ $oi->unit }}" data-description="{{ e($oi->description) }}" @selected((string)($item['order_item_id']??'')===(string)$oi->id)>{{ $oi->description }} · {{ $oi->quantity }} {{ $oi->unit }}</option>@endforeach</select></td>
                <td><select class="js-product" name="items[{{ $idx }}][product_id]"><option value="">Product</option>@foreach(($order?->items?->pluck('product')->filter()->unique('id') ?? collect()) as $p)<option value="{{ $p->id }}" @selected((string)($item['product_id']??'')===(string)$p->id)>{{ $p->name }} · {{ $p->sku }}</option>@endforeach</select></td>
                <td><select class="js-batch" name="items[{{ $idx }}][batch_id]"><option value="">Select batch</option>@foreach($batches as $b)<option value="{{ $b->id }}" data-product="{{ $b->product_id }}" @selected((string)($item['batch_id']??'')===(string)$b->id)>{{ $b->batch_number }} · {{ $b->product?->name }} · Avail {{ rtrim(rtrim(number_format($b->available_quantity,3),'0'),'.') }}</option>@endforeach</select></td>
                <td><input name="items[{{ $idx }}][description]" value="{{ $item['description']??'' }}" placeholder="Bottle / branded water"></td>
                <td><input class="js-qty" type="number" min="0.001" step="0.001" name="items[{{ $idx }}][quantity]" value="{{ $item['quantity']??1 }}" required></td>
                <td><input class="js-unit" name="items[{{ $idx }}][unit]" value="{{ $item['unit']??'bottle' }}" required></td>
                <td><button type="button" class="dlv-icon danger" data-remove-item>×</button></td>
            </tr>
        @endforeach
        </tbody></table></div>
    </section>

    <section class="dlv-section">
        <div class="dlv-section-head"><div><span>04</span><div><h3>Transport</h3><p>Driver and vehicle information for dispatch.</p></div></div></div>
        <div class="dlv-grid dlv-grid-4">
            <label class="dlv-field"><span>Driver Name</span><input name="driver_name" value="{{ old('driver_name',$delivery?->driver_name ?? '') }}"></label>
            <label class="dlv-field"><span>Driver Mobile</span><input name="driver_mobile" value="{{ old('driver_mobile',$delivery?->driver_mobile ?? '') }}"></label>
            <label class="dlv-field"><span>Vehicle Number</span><input name="vehicle_number" value="{{ old('vehicle_number',$delivery?->vehicle_number ?? '') }}"></label>
            <label class="dlv-field dlv-span-1"><span>Dispatch Notes</span><input name="dispatch_notes" value="{{ old('dispatch_notes',$delivery?->dispatch_notes ?? '') }}"></label>
            <label class="dlv-field dlv-span-4"><span>Internal Notes</span><textarea name="notes" rows="3">{{ old('notes',$delivery?->notes ?? '') }}</textarea></label>
        </div>
    </section>

    <div class="dlv-form-actions"><a class="dlv-btn" href="{{ route('admin.deliveries.index') }}">Cancel</a><button class="dlv-btn dlv-dark">{{ $editing ? 'Update Delivery' : 'Create Delivery' }}</button></div>
</div>
