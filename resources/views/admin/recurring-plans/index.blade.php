@extends('layouts.admin')
@section('title', 'Recurring Plans · JALVAN ERP')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/recurring-orders.css') }}">
@endpush
@section('content')
<div class="recurring-page">
    <div class="recurring-page__head">
        <div>
            <div class="recurring-page__eyebrow">Sales · Orders</div>
            <h1 class="recurring-page__title">Recurring Plans</h1>
            <p class="recurring-page__subtitle">Manage repeat bottle-supply schedules and automatic daily orders.</p>
        </div>
        @if(auth()->user()->hasPermission('recurring_plans.create'))
            <a href="{{ route('admin.recurring-plans.create') }}" class="btn btn-primary">+ New Plan</a>
        @endif
    </div>

    <form method="GET" class="recurring-toolbar">
        <div class="recurring-filter">
            <input name="q" value="{{ request('q') }}" class="recurring-input" placeholder="Search plan number, plan name or customer...">
            <select name="status" class="recurring-select">
                <option value="">All status</option>
                @foreach(['draft','active','paused','completed','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['q','status']))
                <a href="{{ route('admin.recurring-plans.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </div>
    </form>

    <div class="recurring-card">
        <div class="recurring-table-wrap">
            <table class="recurring-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Customer</th>
                        <th>Frequency</th>
                        <th>Next Order</th>
                        <th>Daily Orders</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td>
                            <a href="{{ route('admin.recurring-plans.show', $plan) }}">{{ $plan->plan_number }}</a>
                            <span class="recurring-muted">{{ $plan->name }}</span>
                        </td>
                        <td>{{ $plan->customer?->business_name ?? '—' }}</td>
                        <td>{{ ucfirst($plan->frequency) }}</td>
                        <td>{{ $plan->next_order_date?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $plan->daily_orders_count }}</td>
                        <td><span class="recurring-badge {{ $plan->status }}">{{ ucfirst($plan->status) }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-secondary" href="{{ route('admin.recurring-plans.show', $plan) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="recurring-empty">No recurring plans found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="recurring-pagination">{{ $plans->links() }}</div>
    </div>
</div>
@endsection
