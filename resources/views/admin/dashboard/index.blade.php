@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

<div class="page-header">

    <div>
        <h1 class="page-title">
            Good morning, {{ auth()->user()->name ??'' }} 👋
        </h1>

        <div class="page-subtitle">
            Here's what's happening with your business today.
        </div>
    </div>

    <div class="dashboard-actions">

        <a
            href="{{ route('admin.leads.create') }}"
            class="btn btn-primary"
        >
            + New Lead
        </a>

    </div>

</div>


{{-- KPI CARDS --}}

<div class="dashboard-grid">

    <div class="stat-card">

        <div class="stat-top">
            <span>Customers</span>
            <span class="stat-icon">C</span>
        </div>

        <div class="stat-value">
            {{ $stats['customers'] }}
        </div>

        <div class="stat-label">
            Total active customers
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">
            <span>Total Leads</span>
            <span class="stat-icon">L</span>
        </div>

        <div class="stat-value">
            {{ $stats['leads'] }}
        </div>

        <div class="stat-label">
            All business enquiries
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">
            <span>New Leads</span>
            <span class="stat-icon">N</span>
        </div>

        <div class="stat-value">
            {{ $stats['new_leads'] }}
        </div>

        <div class="stat-label">
            Need follow-up
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">
            <span>Products</span>
            <span class="stat-icon">P</span>
        </div>

        <div class="stat-value">
            {{ $stats['products'] }}
        </div>

        <div class="stat-label">
            Active products
        </div>

    </div>

</div>


{{-- LOWER GRID --}}

<div class="dashboard-two-column">

    <div class="card">

        <div class="section-header">

            <div>
                <h3>
                    Recent Leads
                </h3>

                <span>
                    Latest business enquiries
                </span>
            </div>

            <a
                href="#"
                class="section-link"
            >
                View all →
            </a>

        </div>


        @if($recentLeads->count())

    <div class="dashboard-list">

        @foreach($recentLeads as $lead)

            <a
                href="{{ route(
                    'admin.leads.show',
                    $lead
                ) }}"
                class="dashboard-list-item"
            >

                <div class="list-avatar">
                    {{
                        strtoupper(
                            substr(
                                $lead->business_name,
                                0,
                                1
                            )
                        )
                    }}
                </div>

                <div class="list-content">

                    <strong>
                        {{ $lead->business_name }}
                    </strong>

                    <span>
                        {{ $lead->lead_code }}
                        ·
                        {{ $lead->contact_name ?: 'No contact' }}
                    </span>

                </div>

                <span
                    class="
                        lead-status
                        status-{{ $lead->status }}
                    "
                >
                    {{ $lead->status_label }}
                </span>

            </a>

        @endforeach

    </div>

@else

    <div class="empty-state">
        No leads available.
    </div>

@endif

    </div>


    <div class="card">

        <div class="section-header">

            <div>
                <h3>
                    Quick Actions
                </h3>

                <span>
                    Common operations
                </span>
            </div>

        </div>


        <div class="quick-actions">

            <a
                href="{{ route('admin.leads.create') }}"
                class="quick-action"
            >
                <strong>+ New Lead</strong>
                <span>Create business enquiry</span>
            </a>

            <a
                href="#"
                class="quick-action"
            >
                <strong>+ Add Product</strong>
                <span>Add bottle product</span>
            </a>

            <a
                href="#"
                class="quick-action"
            >
                <strong>+ New Design</strong>
                <span>Create design request</span>
            </a>

            <a
                href="#"
                class="quick-action"
            >
                <strong>+ New Order</strong>
                <span>Create customer order</span>
            </a>

        </div>

    </div>

</div>

@endsection