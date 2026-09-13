@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('breadcrumb') Finance / Income & Expenses / Categories / Edit @endsection

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
        <p>Update this category without changing existing Income/Expense amounts.</p>

        @if($errors->any())
            <div class="fec-alert fec-alert-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $type === 'income' ? route('admin.income-expenses.categories.income.update', $category) : route('admin.income-expenses.categories.expense.update', $category) }}" class="fec-form">
            @csrf @method('PUT')
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="fec-form-grid">
                <label>Category Name *<input id="category_name" name="name" value="{{ old('name', $category->name) }}" required maxlength="100"></label>
                <label>Slug *<input id="category_slug" name="slug" value="{{ old('slug', $category->slug) }}" required maxlength="120"></label>
                <label>Status *<select name="status"><option value="active" @selected(old('status', $category->status)==='active')>Active</option><option value="inactive" @selected(old('status', $category->status)==='inactive')>Inactive</option></select></label>
                <label>Sort Order<input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" max="9999"></label>
            </div>
            <div class="fec-form-help">Used in {{ $type }} dropdowns only when the status is Active. Disabling it does not delete existing records.</div>
            <div class="fec-form-actions"><a href="{{ route('admin.income-expenses.categories.index') }}">Cancel</a><button type="submit">Save Changes</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/income-expense-categories.js') }}"></script>
@endpush
