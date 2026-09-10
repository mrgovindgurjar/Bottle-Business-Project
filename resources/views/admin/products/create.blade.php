@extends('layouts.admin')
@section('title','New Product')
@section('page_title','New Product')
@section('breadcrumb')Catalog / Products / New Product@endsection
@section('content')
<div class="product-page-shell">
    <div class="page-header"><div><div class="eyebrow">CATALOG / PRODUCT MASTER</div><h1 class="page-title">Create Product</h1><div class="page-subtitle">Set up a production-ready bottle format before adding its commercial pricing.</div></div></div>
    <div class="product-form-card card"><form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">@include('admin.products._form')</form></div>
</div>
@endsection
