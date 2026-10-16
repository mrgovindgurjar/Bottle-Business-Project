@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Good morning, {{ auth()->user()->name ?? '' }} 👋</h1>
        <div class="page-subtitle">Here's what's happening with your business today.</div>
    </div>
    <div class="dashboard-actions">
        @can('create', \App\Models\Lead::class)
            <a href="{{ route('admin.leads.create') }}" class="btn btn-primary">+ New Lead</a>
        @endcan
        @can('create', \App\Models\Customer::class)
            <a href="{{ route('admin.customers.create') }}" class="btn btn-secondary">+ Customer</a>
        @endcan
    </div>
</div>

<div class="dashboard-grid">
    @can('viewAny', \App\Models\Customer::class)
    <div class="stat-card"><div class="stat-top"><span>Active Customers</span><span class="stat-icon">C</span></div><div class="stat-value">{{ number_format($stats['customers'] ?? 0) }}</div><div class="stat-label">Currently doing business</div></div>
    @endcan
    @can('viewAny', \App\Models\Lead::class)
    <div class="stat-card"><div class="stat-top"><span>Total Leads</span><span class="stat-icon">L</span></div><div class="stat-value">{{ number_format($stats['leads'] ?? 0) }}</div><div class="stat-label">All business enquiries</div></div>
    <div class="stat-card"><div class="stat-top"><span>New Leads</span><span class="stat-icon">N</span></div><div class="stat-value">{{ number_format($stats['new_leads'] ?? 0) }}</div><div class="stat-label">Need attention</div></div>
    <div class="stat-card"><div class="stat-top"><span>Today's Follow-ups</span><span class="stat-icon">F</span></div><div class="stat-value">{{ number_format($stats['followups_today'] ?? 0) }}</div><div class="stat-label">Scheduled for today</div></div>
    @endcan
    @can('viewAny', \App\Models\Product::class)
    <div class="stat-card"><div class="stat-top"><span>Products</span><span class="stat-icon">P</span></div><div class="stat-value">{{ number_format($stats['products'] ?? 0) }}</div><div class="stat-label">Active products</div></div>
    @endcan
    @can('viewAny', \App\Models\Order::class)
    <div class="stat-card"><div class="stat-top"><span>Orders</span><span class="stat-icon">O</span></div><div class="stat-value">{{ number_format($stats['orders'] ?? 0) }}</div><div class="stat-label">Total customer orders</div></div>
    <div class="stat-card"><div class="stat-top"><span>Order Value</span><span class="stat-icon">₹</span></div><div class="stat-value">₹{{ number_format($stats['orders_value'] ?? 0, 0) }}</div><div class="stat-label">This month</div></div>
    @endcan
</div>

<div class="dashboard-two-column">
    @can('viewAny', \App\Models\Lead::class)
    <div class="card">
        <div class="section-header"><div><h3>Recent Leads</h3><span>Latest business enquiries</span></div><a href="{{ route('admin.leads.index') }}" class="section-link">View all →</a></div>
        @if($recentLeads->count())
            <div class="dashboard-list">
                @foreach($recentLeads as $lead)
                    <a href="{{ route('admin.leads.show', $lead) }}" class="dashboard-list-item">
                        <div class="list-avatar">{{ strtoupper(substr($lead->business_name, 0, 1)) }}</div>
                        <div class="list-content"><strong>{{ $lead->business_name }}</strong><span>{{ $lead->lead_code }} · {{ $lead->assignedUser?->name ?: 'Unassigned' }}</span></div>
                        <span class="lead-status status-{{ $lead->status }}">{{ $lead->status_label }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">No leads available.</div>
        @endif
    </div>
    @endcan

    @can('viewAny', \App\Models\Order::class)
    <div class="card">
        <div class="section-header"><div><h3>Recent Orders</h3><span>Latest business orders</span></div><a href="{{ route('admin.orders.index') }}" class="section-link">View all →</a></div>
        @if($recentOrders->count())
            <div class="dashboard-list">
                @foreach($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="dashboard-list-item">
                        <div class="list-avatar">O</div>
                        <div class="list-content"><strong>{{ $order->order_number }}</strong><span>{{ $order->customer?->business_name ?: 'Customer' }} · ₹{{ number_format((float)$order->grand_total, 2) }}</span></div>
                        <span class="lead-status status-{{ $order->status }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">No orders available.</div>
        @endif
    </div>
    @endcan

    @if(auth()->user()->hasPermission('leads.followup'))
    <div class="card">
        <div class="section-header"><div><h3>Today's Follow-ups</h3><span>Leads requiring contact today</span></div><a href="{{ route('admin.leads.index', ['followup_from' => today()->toDateString(), 'followup_to' => today()->toDateString()]) }}" class="section-link">View all →</a></div>
        @if($todayFollowups->count())
            <div class="dashboard-list">
                @foreach($todayFollowups as $lead)
                    <a href="{{ route('admin.leads.show', $lead) }}" class="dashboard-list-item">
                        <div class="list-avatar">{{ strtoupper(substr($lead->business_name, 0, 1)) }}</div>
                        <div class="list-content"><strong>{{ $lead->business_name }}</strong><span>{{ $lead->next_followup_at?->format('h:i A') }} · {{ $lead->assignedUser?->name ?: 'Unassigned' }}</span></div>
                        <span class="lead-status status-{{ $lead->status }}">{{ $lead->status_label }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">No follow-ups scheduled for today.</div>
        @endif
    </div>
    @endif

    <div class="card">
        <div class="section-header"><div><h3>Quick Actions</h3><span>Common operations</span></div></div>
        <div class="quick-actions">
            @can('create', \App\Models\Lead::class)<a href="{{ route('admin.leads.create') }}" class="quick-action"><strong>+ New Lead</strong><span>Create business enquiry</span></a>@endcan
            @can('create', \App\Models\Customer::class)<a href="{{ route('admin.customers.create') }}" class="quick-action"><strong>+ Add Customer</strong><span>Create a business account</span></a>@endcan
            @can('create', \App\Models\Product::class)<a href="{{ route('admin.products.create') }}" class="quick-action"><strong>+ Add Product</strong><span>Add bottle product</span></a>@endcan
            @can('create', \App\Models\Order::class)<a href="{{ route('admin.orders.create') }}" class="quick-action"><strong>+ New Order</strong><span>Create customer order</span></a>@endcan
        </div>
    </div>
</div>
@endsection
