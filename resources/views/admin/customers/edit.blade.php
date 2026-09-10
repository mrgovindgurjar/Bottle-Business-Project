@extends('layouts.admin')
@section('title', 'Edit Customer')
@section('page_title', 'Edit Customer')
@section('breadcrumb')CRM / Customers / Edit@endsection
@section('content')
<div class="customer-page">
    <div class="page-header customer-page-header">
        <div><div class="eyebrow">CUSTOMER PROFILE</div><h1 class="page-title">Edit Customer</h1><div class="page-subtitle">{{ $customer->customer_code }} · {{ $customer->business_name }}</div></div>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-secondary">← Back to Profile</a>
    </div>
    <div class="customer-form-card">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" data-customer-form>@include('admin.customers._form')</form>
    </div>
</div>
@endsection
