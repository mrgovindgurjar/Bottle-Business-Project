@extends('layouts.admin')
@section('title','New Supplier')
@section('page_title','Suppliers')
@section('breadcrumb','Procurement / Suppliers / New')
@push('styles')<link rel="stylesheet" href="{{ asset('css/supplier-purchase.css') }}">@endpush
@section('content')<div class="sp-shell"><div class="sp-head"><div><div class="sp-kicker">VENDOR SETUP</div><h1>New Supplier</h1><p>Create a supplier profile for procurement and stock purchases.</p></div></div><div class="sp-card sp-form-card"><div class="sp-card-head"><div><div class="sp-kicker">SUPPLIER PROFILE</div><h2>Business details</h2></div></div><form method="POST" action="{{ route('admin.suppliers.store') }}">@csrf @include('admin.suppliers._form')</form></div></div>@endsection
