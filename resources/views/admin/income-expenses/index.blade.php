@extends('layouts.admin')
@section('title', 'Income & Expenses')
@section('page_title', 'Income & Expenses')
@section('breadcrumb') Finance / Income & Expenses @endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/income-expenses.css') }}">@endpush
@section('content')
    <div class="fin-wrap">
        <div class="fin-head">
            <div>
                <h1>Income & Expenses</h1>
                <p>Track business income, operating expenses and monthly net movement.</p>
            </div>
            <div class="fin-actions">
                <a class="fin-btn fin-btn-in"
                    href="{{ route('admin.income-expenses.create-income') }}">+ Add Income</a><a class="fin-btn fin-btn-out"
                    href="{{ route('admin.income-expenses.create-expense') }}">+ Add Expense</a>
                <a class="fin-btn" href="{{ route('admin.income-expenses.categories.index') }}">
    ⚙ Categories
</a>
                </div>
        </div>
        <div class="fin-kpis">
            <div><span>Income This Month</span><strong>₹{{ number_format($kpi['income'], 2) }}</strong></div>
            <div><span>Expenses This Month</span><strong>₹{{ number_format($kpi['expense'], 2) }}</strong></div>
            <div><span>Net This Month</span><strong>₹{{ number_format($kpi['net'], 2) }}</strong></div>
        </div>
        <form class="fin-filter"><input name="search" value="{{ $search }}"
                placeholder="Search number or description"><input type="date" name="from" value="{{ $from }}"><input
                type="date" name="to" value="{{ $to }}"><button>Filter</button><a
                href="{{ route('admin.income-expenses.index') }}">Reset</a></form>
        <div class="fin-tabs"><a class="{{ $tab === 'all' ? 'active' : '' }}"
                href="{{ route('admin.income-expenses.index') }}">All</a><a
                href="{{ route('admin.income-expenses.index', ['tab' => 'income']) }}">Income</a><a
                href="{{ route('admin.income-expenses.index', ['tab' => 'expense']) }}">Expenses</a></div>
        @if(session('success'))
        <div class="fin-alert">{{ session('success') }}</div>@endif
        @if($tab !== 'expense')
            <section class="fin-card">
                <div class="fin-card-head">
                    <h2>Income</h2><span>{{ $in->count() }} entries</span>
                </div>
                <div class="fin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Number</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Method</th>
                                <th class="right">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>@forelse($in as $row)
                            <tr>
                                <td>{{ $row->income_date?->format('d M Y') }}</td>
                                <td>{{ $row->income_number }}</td>
                                <td>{{ $row->description }}</td>
                                <td>{{ $row->category?->name }}</td>
                                <td>{{ $row->payment_method }}</td>
                                <td class="right income">₹{{ number_format($row->amount, 2) }}</td>
                                <td><a href="{{ route('admin.income-expenses.show-income', $row) }}">View</a></td>
                        </tr>@empty<tr>
                                <td colspan="7" class="empty">No income recorded yet.</td>
                            </tr>@endforelse
                        </tbody>
                    </table>
                </div>
        </section>@endif
        @if($tab !== 'income')
            <section class="fin-card">
                <div class="fin-card-head">
                    <h2>Expenses</h2><span>{{ $ex->count() }} entries</span>
                </div>
                <div class="fin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Number</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Method</th>
                                <th class="right">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>@forelse($ex as $row)
                            <tr>
                                <td>{{ $row->expense_date?->format('d M Y') }}</td>
                                <td>{{ $row->expense_number }}</td>
                                <td>{{ $row->description }}</td>
                                <td>{{ $row->category?->name }}</td>
                                <td>{{ $row->payment_method }}</td>
                                <td class="right expense">₹{{ number_format($row->amount, 2) }}</td>
                                <td><a href="{{ route('admin.income-expenses.show-expense', $row) }}">View</a></td>
                        </tr>@empty<tr>
                                <td colspan="7" class="empty">No expenses recorded yet.</td>
                            </tr>@endforelse
                        </tbody>
                    </table>
                </div>
        </section>@endif
    </div>

@endsection

@push('scripts')
<script src="{{ asset('js/income-expenses.js') }}"></script>@endpush