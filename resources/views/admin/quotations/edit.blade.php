@extends('layouts.admin')
@section('title','Edit '.$quotation->quotation_number.' — JALVAN ERP')
@section('page_title','Edit Quotation')
@section('breadcrumb','Sales / Quotations / '.$quotation->quotation_number)
@push('styles')<link rel="stylesheet" href="{{ asset('css/quotation.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/quotation.js') }}" defer></script>@endpush
@section('content')
<div class="qt-page"><div class="qt-hero"><div><span class="qt-kicker">{{ $quotation->quotation_number }}</span><h1>Edit Quotation</h1><p>{{ $quotation->customer->business_name }} · update proposal details and pricing.</p></div><div class="qt-hero-actions"><a class="qt-btn qt-btn-light" href="{{ route('admin.quotations.show',$quotation) }}">View</a><a class="qt-btn qt-btn-dark" href="{{ route('admin.quotations.index') }}">← Quotations</a></div></div>
@if($errors->any())<div class="qt-alert qt-alert-error"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@include('admin.quotations._form')
</div>
@endsection
