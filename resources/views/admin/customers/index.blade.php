@extends('layouts.admin')

@section('title', 'Customers')
@section('page_title', 'Customers')
@section('breadcrumb')
    CRM / Customers
@endsection

@section('content')
<div class="customer-page">
    <div class="page-header customer-page-header">
        <div>
            <div class="eyebrow">CUSTOMER RELATIONSHIP MANAGEMENT</div>
            <h1 class="page-title">Customers</h1>
            <div class="page-subtitle">Manage business accounts, contacts, addresses and customer relationships.</div>
        </div>
        @can('create', App\Models\Customer::class)
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary customer-primary-btn">
                <span>＋</span> New Customer
            </a>
        @endcan
    </div>

    <div class="customer-kpis">
        <div class="customer-kpi">
            <div class="customer-kpi-top"><span>Total Customers</span><span class="kpi-icon">◫</span></div>
            <strong>{{ number_format($stats['total']) }}</strong>
            <small>All customer accounts</small>
        </div>
        <div class="customer-kpi">
            <div class="customer-kpi-top"><span>Active</span><span class="kpi-icon kpi-success">✓</span></div>
            <strong>{{ number_format($stats['active']) }}</strong>
            <small>Currently doing business</small>
        </div>
        <div class="customer-kpi">
            <div class="customer-kpi-top"><span>New This Month</span><span class="kpi-icon kpi-blue">↗</span></div>
            <strong>{{ number_format($stats['new']) }}</strong>
            <small>Accounts added this month</small>
        </div>
        <div class="customer-kpi">
            <div class="customer-kpi-top"><span>Blocked</span><span class="kpi-icon kpi-danger">!</span></div>
            <strong>{{ number_format($stats['blocked']) }}</strong>
            <small>Restricted accounts</small>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.customers.index') }}" class="customer-filter-card">
        <div class="customer-search-wrap">
            <span class="customer-search-icon">⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search customer, business, mobile, email, GSTIN..." autocomplete="off">
        </div>
        <select name="status">
            <option value="">All Status</option>
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="city">
            <option value="">All Cities</option>
            @foreach($cities as $city)
                <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
            @endforeach
        </select>
        <select name="source">
            <option value="">All Sources</option>
            @foreach($sources as $source)
                <option value="{{ $source }}" @selected(request('source') === $source)>{{ ucfirst($source) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->hasAny(['search','status','city','source']))
            <a href="{{ route('admin.customers.index') }}" class="customer-clear-filter">Clear</a>
        @endif
    </form>

    <div class="customer-table-card">
        <div class="customer-table-head">
            <div>
                <div class="eyebrow">CUSTOMER DIRECTORY</div>
                <h2>All Customers</h2>
                <span>{{ $customers->total() }} {{ $customers->total() === 1 ? 'record' : 'records' }}</span>
            </div>
            <div class="customer-table-meta">Updated {{ now()->format('d M Y') }}</div>
        </div>

        <div class="customer-table-scroll">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Business</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer) }}" class="customer-identity">
                                    <span class="customer-avatar">{{ strtoupper(substr($customer->business_name, 0, 1)) }}</span>
                                    <span>
                                        <strong>{{ $customer->business_name }}</strong>
                                        <small>{{ $customer->customer_code }}</small>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <div class="customer-contact">
                                    <strong>{{ $customer->user?->name ?: '—' }}</strong>
                                    <span>{{ $customer->user?->mobile ?: $customer->user?->email ?: '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="customer-location">
                                    <strong>{{ $customer->city ?: '—' }}</strong>
                                    <span>{{ $customer->state ?: 'Location not added' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="customer-business-type">{{ $customer->business_type ?: 'Business' }}</span>
                            </td>
                            <td>
                                <span class="customer-status status-{{ $customer->status }}">
                                    <i></i>{{ $customer->status_label }}
                                </span>
                            </td>
                            <td>
                                <span class="customer-date">{{ $customer->created_at->format('d M Y') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.customers.show', $customer) }}" class="customer-view-btn">View <span>→</span></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="customer-empty">
                                    <div class="customer-empty-icon">◌</div>
                                    <strong>No customers found</strong>
                                    <span>Start building your customer database or adjust the filters.</span>
                                    @can('create', App\Models\Customer::class)
                                        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">＋ Add First Customer</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="customer-pagination">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection
