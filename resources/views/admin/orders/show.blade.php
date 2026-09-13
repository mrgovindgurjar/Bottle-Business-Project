@extends('layouts.admin')
@section('title',$order->order_number)
@section('page_title','Order Details')
@section('breadcrumb') Sales / Orders / {{ $order->order_number }} @endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/order.css') }}">@endpush
@section('content')
<div class="ord-page ord-detail-page">
    <div class="ord-page-head"><div><span class="ord-kicker">ORDER · {{ $order->order_number }}</span><h1>{{ $order->customer->business_name }}</h1><p>{{ $order->order_date?->format('d M Y') }} · {{ $order->items->count() }} line items</p></div><div class="ord-head-actions"><span class="ord-badge ord-badge-{{ $order->status }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span><a class="ord-btn ord-btn-light" href="{{ route('admin.orders.index') }}">← Orders</a></div></div>
    @if(session('success'))<div class="ord-alert ord-alert-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="ord-alert ord-alert-error">{{ session('error') }}</div>@endif
    <div class="ord-action-bar">
        @if($order->isEditable()) @can('update',$order)<a class="ord-btn ord-btn-light" href="{{ route('admin.orders.edit',$order) }}">Edit</a>@endcan @endif
        @can('duplicate',$order)<form method="POST" action="{{ route('admin.orders.duplicate',$order) }}">@csrf<button class="ord-btn ord-btn-light">Duplicate</button></form>@endcan
        @can('export',$order)<a class="ord-btn ord-btn-light" target="_blank" href="{{ route('admin.orders.preview',$order) }}">Print Preview</a><a class="ord-btn ord-btn-light" href="{{ route('admin.orders.pdf',$order) }}">PDF</a>@endcan
        @can('confirm',$order)<form method="POST" action="{{ route('admin.orders.confirm',$order) }}">@csrf<button class="ord-btn ord-btn-dark">Confirm Order →</button></form>@endcan
        @can('changeStatus',$order)
        <form method="POST" action="{{ route('admin.orders.status',$order) }}" class="ord-status-form">@csrf<select name="status"><option value="">Change status…</option>@foreach(['in_production','ready','dispatched','delivered','on_hold'] as $next)<option value="{{ $next }}">{{ ucwords(str_replace('_',' ',$next)) }}</option>@endforeach</select><button class="ord-btn ord-btn-light">Update</button></form>
        @endcan
        @can('cancel',$order)<form method="POST" action="{{ route('admin.orders.cancel',$order) }}" onsubmit="return confirm('Cancel this order?')">@csrf<button class="ord-btn ord-btn-danger">Cancel</button></form>@endcan
    </div>

    <div class="ord-detail-grid">
        <div>
            <section class="ord-card"><div class="ord-card-head"><div><span class="ord-kicker">CUSTOMER</span><h3>Customer Details</h3></div></div><div class="ord-info-grid"><div><span>Business</span><strong>{{ $order->customer->business_name }}</strong></div><div><span>Customer Code</span><strong>{{ $order->customer->customer_code }}</strong></div><div><span>Mobile</span><strong>{{ $order->customer->user?->mobile ?: '—' }}</strong></div><div><span>Email</span><strong>{{ $order->customer->user?->email ?: '—' }}</strong></div></div></section>
            <section class="ord-card"><div class="ord-card-head"><div><span class="ord-kicker">ITEMS</span><h3>Order Items</h3></div></div><div class="ord-table-wrap"><table class="ord-items ord-detail-items"><thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Tax</th><th>Total</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><strong>{{ $item->product?->name ?: 'Custom item' }}</strong><small>{{ $item->product?->sku ?: '—' }}</small></td><td>{{ $item->description }}</td><td>{{ rtrim(rtrim(number_format($item->quantity,3,'.',''), '0'),'.') }} {{ $item->unit }}</td><td>₹{{ number_format($item->unit_price,2) }}</td><td>₹{{ number_format($item->discount_amount,2) }}</td><td>₹{{ number_format($item->tax_amount,2) }}<small>{{ number_format($item->tax_rate,2) }}%</small></td><td><strong>₹{{ number_format($item->line_total,2) }}</strong></td></tr>@endforeach</tbody></table></div></section>
            <section class="ord-card"><div class="ord-card-head"><div><span class="ord-kicker">DELIVERY</span><h3>Delivery Snapshot</h3></div></div><div class="ord-address"><strong>{{ $order->delivery_address ?: 'No delivery address captured.' }}</strong><span>{{ collect([$order->delivery_city,$order->delivery_state,$order->delivery_pincode])->filter()->implode(', ') }}</span></div></section>
        </div>
        <aside>
            <section class="ord-card ord-total-card ord-sticky"><div class="ord-card-head"><div><span class="ord-kicker">ORDER VALUE</span><h3>Financial Summary</h3></div></div><div><span>Subtotal</span><strong>₹{{ number_format($order->subtotal,2) }}</strong></div><div><span>Discount</span><strong>− ₹{{ number_format($order->discount_amount,2) }}</strong></div><div><span>Tax</span><strong>₹{{ number_format($order->tax_amount,2) }}</strong></div><div><span>Shipping</span><strong>₹{{ number_format($order->shipping_amount,2) }}</strong></div><div><span>Other</span><strong>₹{{ number_format($order->other_amount,2) }}</strong></div><div class="ord-grand"><span>Grand Total</span><strong>₹{{ number_format($order->grand_total,2) }}</strong></div></section>
            <section class="ord-card"><div class="ord-card-head"><div><span class="ord-kicker">REFERENCE</span><h3>Sales Source</h3></div></div>@if($order->quotation)<a class="ord-reference" href="{{ route('admin.quotations.show',$order->quotation) }}"><span>Quotation</span><strong>{{ $order->quotation->quotation_number }}</strong><small>Approved quotation source</small></a>@else<div class="ord-muted">Manual order — no quotation linked.</div>@endif @if($order->design)<div class="ord-reference"><span>Design Studio</span><strong>{{ $order->design->design_code }}</strong><small>{{ $order->design->title }}</small></div>@endif</section>
            <section class="ord-card"><div class="ord-card-head"><div><span class="ord-kicker">TIMELINE</span><h3>Order History</h3></div></div><div class="ord-timeline"><div><i></i><span>Created</span><small>{{ $order->created_at?->format('d M Y, h:i A') }}</small></div>@if($order->confirmed_at)<div><i></i><span>Confirmed</span><small>{{ $order->confirmed_at->format('d M Y, h:i A') }}</small></div>@endif @if($order->cancelled_at)<div><i></i><span>Cancelled</span><small>{{ $order->cancelled_at->format('d M Y, h:i A') }}</small></div>@endif</div></section>
        </aside>
    </div>
    @if($order->notes || $order->internal_notes)<div class="ord-notes-grid"><section class="ord-card"><span class="ord-kicker">CUSTOMER NOTES</span><p>{{ $order->notes ?: '—' }}</p></section><section class="ord-card"><span class="ord-kicker">INTERNAL NOTES</span><p>{{ $order->internal_notes ?: '—' }}</p></section></div>@endif
</div>
@endsection
