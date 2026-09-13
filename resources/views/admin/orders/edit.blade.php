@extends('layouts.admin')
@section('title','Edit '.$order->order_number)
@section('page_title','Orders')
@section('breadcrumb') Sales / Orders / {{ $order->order_number }} / Edit @endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/order.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/order.js') }}" defer></script>@endpush
@section('content')
<div class="ord-page">
    <div class="ord-page-head"><div><span class="ord-kicker">ORDER · {{ $order->order_number }}</span><h1>Edit Order</h1><p>Only draft and on-hold orders can be edited.</p></div><a class="ord-btn ord-btn-light" href="{{ route('admin.orders.show',$order) }}">← Order Details</a></div>
    @if($errors->any())<div class="ord-alert ord-alert-error"><strong>Please check the form.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @include('admin.orders._form')
</div>
@endsection
