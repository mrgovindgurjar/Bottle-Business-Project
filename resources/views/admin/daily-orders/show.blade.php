@extends('layouts.admin')
@section('title', $dailyOrder->daily_order_number.' · JALVAN ERP')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/recurring-orders.css') }}">
@endpush
@section('content')
<div class="recurring-page">
    <div class="recurring-page__head">
        <div>
            <div class="recurring-page__eyebrow">Daily Order</div>
            <h1 class="recurring-page__title">{{ $dailyOrder->daily_order_number }}</h1>
            <p class="recurring-page__subtitle">{{ $dailyOrder->customer?->business_name }} · {{ $dailyOrder->scheduled_date?->format('d M Y') }}</p>
        </div>
        <div class="recurring-actions">
            @if($dailyOrder->status === 'pending' && auth()->user()->hasPermission('daily_orders.approve'))
                <form method="POST" action="{{ route('admin.daily-orders.convert', $dailyOrder) }}" data-confirm-action="Generate a confirmed Order from this Daily Order?">
                    @csrf
                    <button class="btn btn-primary">Generate Order</button>
                </form>
            @endif
            @if($dailyOrder->status === 'pending' && auth()->user()->hasPermission('daily_orders.edit'))
                <form method="POST" action="{{ route('admin.daily-orders.cancel', $dailyOrder) }}" data-confirm-action="Cancel this Daily Order?">
                    @csrf
                    <button class="btn btn-secondary">Cancel</button>
                </form>
            @endif
        </div>
    </div>

    <div class="daily-summary">
        <div class="daily-summary__card"><small>Status</small><strong><span class="recurring-badge {{ $dailyOrder->status }}">{{ ucfirst($dailyOrder->status) }}</span></strong></div>
        <div class="daily-summary__card"><small>Scheduled Date</small><strong>{{ $dailyOrder->scheduled_date?->format('d M Y') ?? '—' }}</strong></div>
        <div class="daily-summary__card"><small>Grand Total</small><strong>₹{{ number_format((float)$dailyOrder->grand_total, 2) }}</strong></div>
    </div>

    <div class="recurring-card">
        <div class="recurring-table-wrap">
            <table class="recurring-table">
                <thead><tr><th>Product</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($dailyOrder->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? '—' }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        <td>₹{{ number_format((float)$item->unit_price, 2) }}</td>
                        <td>{{ $item->tax_rate }}%</td>
                        <td>₹{{ number_format((float)$item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="recurring-empty">No items found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
