@extends('layouts.admin')
@section('title','New Quotation — JALVAN ERP')
@section('page_title','New Quotation')
@section('breadcrumb','Sales / Quotations / Create')
@push('styles')<link rel="stylesheet" href="{{ asset('css/quotation.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/quotation.js') }}" defer></script>@endpush
@section('content')
<div class="qt-page"><div class="qt-hero"><div><span class="qt-kicker">SALES PROPOSAL</span><h1>Create Quotation</h1><p>Build a polished proposal with customer, product, pricing and design details.</p></div><a class="qt-btn qt-btn-light" href="{{ route('admin.quotations.index') }}">← Quotations</a></div>
@if($errors->any())<div class="qt-alert qt-alert-error"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@include('admin.quotations._form')</div>
@endsection
