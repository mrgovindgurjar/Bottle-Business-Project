@extends('layouts.admin')
@section('title','Edit '.$delivery->delivery_number)
@section('page_title','Edit Delivery')
@section('breadcrumb','Delivery / '.$delivery->delivery_number)
@push('styles')<link rel="stylesheet" href="{{ asset('css/delivery.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/delivery.js') }}" defer></script>@endpush
@section('content')
<div class="dlv-page"><div class="dlv-page-head"><div><span class="dlv-kicker">DELIVERY · EDIT</span><h1>{{ $delivery->delivery_number }}</h1><p>{{ $delivery->customer->business_name }} · {{ $delivery->order->order_number }}</p></div></div>
@if($errors->any())<div class="dlv-alert dlv-error">Please correct the highlighted fields.<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.deliveries.update',$delivery) }}">@csrf @method('PUT') @include('admin.deliveries._form')</form></div>
<script>window.JALVAN_DELIVERY={{ \Illuminate\Support\Js::from(['orders'=>$orders,'batches'=>$batches]) }};</script>
@endsection
