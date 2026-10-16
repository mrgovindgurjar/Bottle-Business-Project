@extends('layouts.admin')
@section('title', 'Daily Orders · JALVAN ERP')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/recurring-orders.css') }}">
@endpush
@section('content')
<div class="recurring-page">
    <div class="recurring-page__head">
        <div>
            <div class="recurring-page__eyebrow">Orders · Scheduled</div>
            <h1 class="recurring-page__title">Daily Orders</h1>
            <p class="recurring-page__subtitle">Review scheduled orders generated from recurring plans.</p>
        </div>
        @if(auth()->user()->hasPermission('daily_orders.create'))
            <form method="POST" action="{{ route('admin.daily-orders.generate') }}" class="recurring-actions">
                @csrf
                <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}" class="recurring-input" style="width:170px">
                <button class="btn btn-primary">Generate Due Orders</button>
            </form>
        @endif
    </div>

    <div class="recurring-toolbar">
        <form method="GET" class="recurring-filter">
            <input name="q" value="{{ request('q') }}" class="recurring-input" placeholder="Search daily order or customer...">
            <select name="status" class="recurring-select">
                <option value="">All status</option>
                @foreach(['pending','generated','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="recurring-input">
            <button class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="recurring-card">
        <div class="recurring-table-wrap">
            <table class="recurring-table">
                <thead><tr><th>Daily Order</th><th>Customer</th><th>Scheduled</th><th>Plan</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($orders as $daily)
                    <tr>
                        <td><a href="{{ route('admin.daily-orders.show', $daily) }}">{{ $daily->daily_order_number }}</a></td>
                        <td>{{ $daily->customer?->business_name ?? '—' }}</td>
                        <td>{{ $daily->scheduled_date?->format('d M Y') ?? '—' }}</td>
                        <td>{{ $daily->plan?->plan_number ?? '—' }}</td>
                        <td>₹{{ number_format((float)$daily->grand_total, 2) }}</td>
                        <td><span class="recurring-badge {{ $daily->status }}">{{ ucfirst($daily->status) }}</span></td>
                        <td class="text-end"><a class="btn btn-secondary" href="{{ route('admin.daily-orders.show', $daily) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="recurring-empty">No daily orders found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="recurring-pagination">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
