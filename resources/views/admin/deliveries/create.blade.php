@extends('layouts.admin')
@section('title','New Delivery')
@section('page_title','New Delivery')
@section('breadcrumb','Delivery / New')
@push('styles')<link rel="stylesheet" href="{{ asset('css/delivery.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/delivery.js') }}" defer></script>@endpush
@section('content')
<div class="dlv-page">
    <div class="dlv-page-head"><div><span class="dlv-kicker">FULFILMENT · DISPATCH</span><h1>Create Delivery</h1><p>Create a dispatch record from a confirmed/production order.</p></div></div>
    @if($errors->any())<div class="dlv-alert dlv-error">Please correct the highlighted fields.<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('admin.deliveries.store') }}">@csrf @include('admin.deliveries._form')</form>
</div>
<script>window.JALVAN_DELIVERY={{ \Illuminate\Support\Js::from(['orders'=>$orders,'batches'=>$batches]) }};</script>
@endsection
