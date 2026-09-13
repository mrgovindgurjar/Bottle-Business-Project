@extends('layouts.admin')
@section('title','Edit '.$supplier->business_name)
@section('page_title','Suppliers')
@section('breadcrumb','Procurement / Suppliers / Edit')
@push('styles')<link rel="stylesheet" href="{{ asset('css/supplier-purchase.css') }}">@endpush
@section('content')<div class="sp-shell"><div class="sp-head"><div><div class="sp-kicker">SUPPLIER {{ $supplier->supplier_code }}</div><h1>Edit Supplier</h1><p>Update vendor information.</p></div></div><div class="sp-card sp-form-card"><form method="POST" action="{{ route('admin.suppliers.update',$supplier) }}">@csrf @method('PUT') @include('admin.suppliers._form')</form></div></div>@endsection
