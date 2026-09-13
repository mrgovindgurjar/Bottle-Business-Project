@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('breadcrumb') Finance / Income & Expenses / Categories / Create @endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/income-expense-categories.css') }}">
@endpush
@section('content')

<div class="fec-form-page">
    <div class="fec-form-card {{ $type === 'income' ? 'is-income' : 'is-expense' }}">
        <div class="fec-form-top">
            <a class="fec-back" href="{{ route('admin.income-expenses.categories.index') }}">← Back to Categories</a>
            <span class="fec-type-badge">{{ ucfirst($type) }} Category</span>
        </div>
        <h1>{{ $title }}</h1>
        <p>Create a reusable category for your {{ $type }} entries.</p>

        @if($errors->any())
            <div class="fec-alert fec-alert-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $type === 'income' ? route('admin.income-expenses.categories.income.store') : route('admin.income-expenses.categories.expense.store') }}" class="fec-form">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="fec-form-grid">
                <label>Category Name *<input id="category_name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Bottle Sales"></label>
                <label>Slug *<input id="category_slug" name="slug" value="{{ old('slug') }}" required maxlength="120" placeholder="bottle-sales"></label>
                <label>Status *<select name="status"><option value="active" @selected(old('status','active')==='active')>Active</option><option value="inactive" @selected(old('status')==='inactive')>Inactive</option></select></label>
                <label>Sort Order<input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999"></label>
            </div>
            <div class="fec-form-help">Slug is used internally and must be unique within {{ $type }} categories. You can change the display name later without changing existing transactions.</div>
            <div class="fec-form-actions"><a href="{{ route('admin.income-expenses.categories.index') }}">Cancel</a><button type="submit">Create Category</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/income-expense-categories.js') }}"></script>
@endpush
