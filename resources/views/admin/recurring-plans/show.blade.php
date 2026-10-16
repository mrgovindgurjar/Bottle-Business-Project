@extends('layouts.admin')
@section('title', $p->plan_number.' · JALVAN ERP')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/recurring-orders.css') }}">
@endpush
@section('content')
<div class="recurring-page">
    <div class="recurring-page__head">
        <div>
            <div class="recurring-page__eyebrow">Recurring Plan</div>
            <h1 class="recurring-page__title">{{ $p->plan_number }}</h1>
            <p class="recurring-page__subtitle">{{ $p->name }} · {{ $p->customer?->business_name }}</p>
        </div>
        <div class="recurring-actions">
            @if($p->status === 'draft' && auth()->user()->hasPermission('recurring_plans.approve'))
                <form method="POST" action="{{ route('admin.recurring-plans.approve', $p) }}">@csrf<button class="btn btn-primary">Approve</button></form>
            @endif
            @if($p->status === 'active')
                <form method="POST" action="{{ route('admin.recurring-plans.pause', $p) }}" data-confirm-action="Pause this recurring plan?">@csrf<button class="btn btn-secondary">Pause</button></form>
            @elseif($p->status === 'paused')
                <form method="POST" action="{{ route('admin.recurring-plans.resume', $p) }}">@csrf<button class="btn btn-primary">Resume</button></form>
            @endif
            @if(in_array($p->status, ['draft','paused'], true) && auth()->user()->hasPermission('recurring_plans.edit'))
                <a class="btn btn-secondary" href="{{ route('admin.recurring-plans.edit', $p) }}">Edit</a>
            @endif
        </div>
    </div>

    <div class="recurring-detail-grid">
        <div class="recurring-card recurring-detail-card">
            <div class="recurring-detail-list">
                <div><small>Status</small><strong><span class="recurring-badge {{ $p->status }}">{{ ucfirst($p->status) }}</span></strong></div>
                <div><small>Frequency</small><strong>{{ ucfirst($p->frequency) }}</strong></div>
                <div><small>Start Date</small><strong>{{ $p->start_date?->format('d M Y') ?? '—' }}</strong></div>
                <div><small>End Date</small><strong>{{ $p->end_date?->format('d M Y') ?? 'No end date' }}</strong></div>
                <div><small>Next Order</small><strong>{{ $p->next_order_date?->format('d M Y') ?? '—' }}</strong></div>
                <div><small>Auto Generate</small><strong>{{ $p->auto_generate ? 'Yes' : 'No' }}</strong></div>
                <div><small>Customer</small><strong>{{ $p->customer?->business_name ?? '—' }}</strong></div>
                <div><small>Items</small><strong>{{ $p->items->count() }}</strong></div>
            </div>
        </div>

        <div class="recurring-card recurring-detail-card">
            <div class="recurring-page__eyebrow">Delivery</div>
            <p style="margin:8px 0 0;font-size:11px;line-height:1.6;color:#475467;white-space:pre-wrap">{{ $p->delivery_address ?: 'No delivery address saved.' }}</p>
            @if($p->notes)
                <div class="recurring-page__eyebrow" style="margin-top:18px">Notes</div>
                <p style="margin:8px 0 0;font-size:11px;line-height:1.6;color:#475467;white-space:pre-wrap">{{ $p->notes }}</p>
            @endif
        </div>
    </div>

    <div class="recurring-card" style="margin-top:16px">
        <div class="recurring-section__head" style="padding:18px 20px;margin:0;border-bottom:1px solid #edf0f3">
            <div><h3>Recent Daily Orders</h3><p>Orders generated from this recurring schedule.</p></div>
        </div>
        <div class="recurring-table-wrap">
            <table class="recurring-table">
                <thead><tr><th>Number</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($p->dailyOrders as $daily)
                    <tr>
                        <td><a href="{{ route('admin.daily-orders.show', $daily) }}">{{ $daily->daily_order_number }}</a></td>
                        <td>{{ $daily->scheduled_date?->format('d M Y') ?? '—' }}</td>
                        <td><span class="recurring-badge {{ $daily->status }}">{{ ucfirst($daily->status) }}</span></td>
                        <td>₹{{ number_format((float)$daily->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="recurring-empty">No daily orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
