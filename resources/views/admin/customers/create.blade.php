@extends('layouts.admin')
@section('title', 'New Customer')
@section('page_title', 'New Customer')
@section('breadcrumb')CRM / Customers / New Customer@endsection
@section('content')
<div class="customer-page">
    <div class="page-header customer-page-header">
        <div><div class="eyebrow">CUSTOMER ONBOARDING</div><h1 class="page-title">Create Customer</h1><div class="page-subtitle">Set up the business account once. Orders, designs, deliveries and billing will attach to it.</div></div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">← Back to Customers</a>
    </div>
    <div class="customer-form-card">
        <form method="POST" action="{{ route('admin.customers.store') }}" data-customer-form>@include('admin.customers._form')</form>
    </div>
</div>
@endsection
