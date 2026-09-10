@extends('layouts.admin')
@section('title','Edit Product')
@section('page_title','Edit Product')
@section('breadcrumb')Catalog / Products / Edit@endsection
@section('content')
<div class="product-page-shell">
    <div class="page-header"><div><div class="eyebrow">CATALOG / PRODUCT MASTER</div><h1 class="page-title">Edit Product</h1><div class="page-subtitle">Update specifications without changing historical order prices.</div></div><a href="{{ route('admin.products.show',$product) }}" class="btn btn-secondary">← Back to Product</a></div>
    <div class="product-form-card card"><form method="POST" action="{{ route('admin.products.update',$product) }}" enctype="multipart/form-data">@method('PUT') @include('admin.products._form')</form></div>
</div>
@endsection
