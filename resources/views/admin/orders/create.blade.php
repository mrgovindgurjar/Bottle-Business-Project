@extends('layouts.admin')
@section('title','New Order')
@section('page_title','Orders')
@section('breadcrumb') Sales / Orders / New @endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/order.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/order.js') }}" defer></script>@endpush
@section('content')
<div class="ord-page">
    <div class="ord-page-head"><div><span class="ord-kicker">SALES · ORDER MANAGEMENT</span><h1>Create Order</h1><p>Create a customer order manually or load an approved quotation.</p></div><a class="ord-btn ord-btn-light" href="{{ route('admin.orders.index') }}">← All Orders</a></div>
    @if($errors->any())<div class="ord-alert ord-alert-error"><strong>Please check the form.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @include('admin.orders._form')
</div>
@endsection
