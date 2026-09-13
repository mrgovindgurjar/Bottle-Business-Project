@extends('layouts.admin')
@section('title','Orders')
@section('page_title','Orders')
@section('breadcrumb') Sales / Orders @endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/order.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/order.js') }}" defer></script>@endpush
@section('content')
<div class="ord-page">
    <div class="ord-page-head"><div><span class="ord-kicker">SALES · FULFILMENT</span><h1>Orders</h1><p>Manage confirmed orders from quotation through delivery.</p></div><a class="ord-btn ord-btn-dark" href="{{ route('admin.orders.create') }}">+ New Order</a></div>

    <div class="ord-stats">
        @foreach([
            ['Total Orders',$stats['total'],'All customer orders','total'],
            ['Drafts',$stats['draft'],'Need confirmation','draft'],
            ['Confirmed',$stats['confirmed'],'Ready for production','confirmed'],
            ['In Production',$stats['production'],'Currently being made','in_production'],
            ['Ready',$stats['ready'],'Ready to dispatch','ready'],
            ['Delivered',$stats['delivered'],'Completed orders','delivered'],
        ] as $stat)
        <a class="ord-stat" href="{{ $stat[3]==='total' ? route('admin.orders.index') : route('admin.orders.index',['status'=>$stat[3]]) }}"><div><span>{{ $stat[0] }}</span><b>{{ $stat[1] }}</b><small>{{ $stat[2] }}</small></div><i>{{ strtoupper(substr($stat[0],0,1)) }}</i></a>
        @endforeach
    </div>
    <div class="ord-value-strip"><div><span>Total Order Value</span><strong>₹{{ number_format($stats['value'],2) }}</strong></div><div><span>Production pipeline</span><strong>{{ $stats['production'] + $stats['ready'] }} orders</strong></div><div><span>Dispatched</span><strong>{{ $stats['dispatched'] }}</strong></div></div>

    <form method="GET" class="ord-filter-card">
        <div class="ord-filter-search"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Search order, customer, mobile..."></div>
        <select name="status"><option value="">All Status</option>@foreach(['draft','confirmed','in_production','ready','dispatched','delivered','on_hold','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select>
        <select name="customer_id"><option value="">All Customers</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string)request('customer_id')===(string)$customer->id)>{{ $customer->business_name }}</option>@endforeach</select>
        <input type="date" name="date_from" value="{{ request('date_from') }}"><input type="date" name="date_to" value="{{ request('date_to') }}">
        <button class="ord-btn ord-btn-dark">Search</button><a class="ord-btn ord-btn-light" href="{{ route('admin.orders.index') }}">Reset</a>
    </form>

    <section class="ord-list-card"><div class="ord-list-head"><div><span class="ord-kicker">ORDER PIPELINE</span><h2>All Orders</h2><small>{{ $orders->total() }} records</small></div></div>
        @if($orders->count())
        <div class="ord-table-wrap"><table class="ord-list"><thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Items</th><th>Amount</th><th>Status</th><th>Created By</th><th>Actions</th></tr></thead><tbody>
        @foreach($orders as $order)<tr><td><a class="ord-number" href="{{ route('admin.orders.show',$order) }}">{{ $order->order_number }}</a><small>{{ $order->quotation?->quotation_number ? 'From '.$order->quotation->quotation_number : 'Manual order' }}</small></td><td><strong>{{ $order->customer->business_name }}</strong><small>{{ $order->customer->customer_code }} · {{ $order->customer->user?->mobile ?: 'No mobile' }}</small></td><td>{{ $order->order_date?->format('d M Y') }}<small>{{ $order->required_date ? 'Due '.$order->required_date->format('d M Y') : 'No deadline' }}</small></td><td>{{ $order->items_count }}</td><td><strong>₹{{ number_format($order->grand_total,2) }}</strong></td><td><span class="ord-badge ord-badge-{{ $order->status }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span></td><td>{{ $order->creator?->name ?? '—' }}</td><td><div class="ord-actions"><a href="{{ route('admin.orders.show',$order) }}">View</a><form method="POST" action="{{ route('admin.orders.duplicate',$order) }}">@csrf<button type="submit">Duplicate</button></form></div></td></tr>@endforeach
        </tbody></table></div>
        <div class="ord-pagination">{{ $orders->links() }}</div>
        @else
        <div class="ord-empty"><div>O</div><h3>No orders yet</h3><p>Create your first order or convert an approved quotation.</p><a class="ord-btn ord-btn-dark" href="{{ route('admin.orders.create') }}">+ New Order</a></div>
        @endif
    </section>
</div>
@endsection
