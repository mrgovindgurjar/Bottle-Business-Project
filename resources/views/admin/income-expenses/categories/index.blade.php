@extends('layouts.admin')

@section('title', 'Income & Expense Categories')
@section('page_title', 'Income & Expense Categories')
@section('breadcrumb') Finance / Income & Expenses / Categories @endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/income-expense-categories.css') }}">
@endpush
@section('content')

<div class="fec-wrap">
    <div class="fec-head">
        <div>
            <div class="fec-eyebrow">FINANCE CONTROL</div>
            <h1>Income & Expense Categories</h1>
            <p>Manage the categories used when recording business income and expenses.</p>
        </div>
        <div class="fec-actions">
            <a class="fec-btn fec-btn-income" href="{{ route('admin.income-expenses.categories.income.create') }}">+ Income Category</a>
            <a class="fec-btn fec-btn-expense" href="{{ route('admin.income-expenses.categories.expense.create') }}">+ Expense Category</a>
        </div>
    </div>

    @if(session('success'))
        <div class="fec-alert fec-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="fec-alert fec-alert-error">Please check the form and try again.</div>
    @endif

    <div class="fec-kpis">
        <div class="fec-kpi"><span>Income Categories</span><strong>{{ $incomeCategories->count() }}</strong><small>{{ $incomeCategories->where('status','active')->count() }} active</small></div>
        <div class="fec-kpi"><span>Expense Categories</span><strong>{{ $expenseCategories->count() }}</strong><small>{{ $expenseCategories->where('status','active')->count() }} active</small></div>
        <div class="fec-kpi"><span>Total Categories</span><strong>{{ $incomeCategories->count() + $expenseCategories->count() }}</strong><small>Across both ledgers</small></div>
    </div>

    <form class="fec-filter" method="GET" action="{{ route('admin.income-expenses.categories.index') }}">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search category or slug">
        <select name="status">
            <option value="">All statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
        </select>
        <button type="submit">Filter</button>
        @if($search || $status)
            <a href="{{ route('admin.income-expenses.categories.index') }}">Reset</a>
        @endif
    </form>

    <div class="fec-grid">
        <section class="fec-card">
            <div class="fec-card-head">
                <div><span class="fec-dot fec-dot-income"></span><h2>Income Categories</h2><p>Categories for money received by JALVAN.</p></div>
                <a href="{{ route('admin.income-expenses.categories.income.create') }}">Add</a>
            </div>
            <div class="fec-table-wrap">
                <table class="fec-table">
                    <thead><tr><th>Category</th><th>Slug</th><th>Entries</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($incomeCategories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong><small>Order {{ $category->sort_order }}</small></td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->incomes_count }}</td>
                            <td><span class="fec-status {{ $category->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($category->status) }}</span></td>
                            <td class="fec-row-actions">
                                <a href="{{ route('admin.income-expenses.categories.income.edit', $category) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.income-expenses.categories.income.toggle', $category) }}" data-confirm="Change this category status?">
                                    @csrf @method('PATCH')
                                    <button type="submit">{{ $category->status === 'active' ? 'Disable' : 'Enable' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="fec-empty">No income categories found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="fec-card">
            <div class="fec-card-head">
                <div><span class="fec-dot fec-dot-expense"></span><h2>Expense Categories</h2><p>Categories for business spending and operating costs.</p></div>
                <a href="{{ route('admin.income-expenses.categories.expense.create') }}">Add</a>
            </div>
            <div class="fec-table-wrap">
                <table class="fec-table">
                    <thead><tr><th>Category</th><th>Slug</th><th>Entries</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($expenseCategories as $category)
                        <tr>
                            <td><strong>{{ $category->name }}</strong><small>Order {{ $category->sort_order }}</small></td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->expenses_count }}</td>
                            <td><span class="fec-status {{ $category->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($category->status) }}</span></td>
                            <td class="fec-row-actions">
                                <a href="{{ route('admin.income-expenses.categories.expense.edit', $category) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.income-expenses.categories.expense.toggle', $category) }}" data-confirm="Change this category status?">
                                    @csrf @method('PATCH')
                                    <button type="submit">{{ $category->status === 'active' ? 'Disable' : 'Enable' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="fec-empty">No expense categories found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="fec-note"><strong>Tip:</strong> Disable a category instead of deleting it. Existing Income/Expense history remains linked safely.</div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/income-expense-categories.js') }}"></script>
@endpush
