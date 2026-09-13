@extends('layouts.admin')
@section('title','Preview '.$quotation->quotation_number)
@section('page_title','Quotation Preview')
@section('breadcrumb','Sales / Quotations / Preview')
@push('styles')<link rel="stylesheet" href="{{ asset('css/quotation.css') }}">@endpush
@section('content')
<div class="qt-page qt-preview-page"><div class="qt-preview-toolbar"><a class="qt-btn qt-btn-light" href="{{ route('admin.quotations.show',$quotation) }}">← Back</a><div><button class="qt-btn qt-btn-dark" onclick="window.print()">Print</button><a class="qt-btn qt-btn-primary" href="{{ route('admin.quotations.pdf',$quotation) }}">Download PDF</a></div></div>
@include('admin.quotations._document')
</div>
@endsection
