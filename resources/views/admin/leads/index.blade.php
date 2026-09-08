    @extends('layouts.admin')

@section('title', 'Leads')
@section('page_title', 'Leads')

@section('breadcrumb')
    CRM / Leads
@endsection

@section('content')

<div class="page-header">

    <div>
        <h1 class="page-title">
            Leads
        </h1>

        <div class="page-subtitle">
            Manage enquiries, follow-ups and sales opportunities.
        </div>
    </div>

    <a
        href="{{ route('admin.leads.create') }}"
        class="btn btn-primary"
    >
        + New Lead
    </a>

</div>


{{-- STATS --}}

<div class="lead-stats">

    <div class="lead-stat-card">

        <span>Total Leads</span>

        <strong>
            {{ $stats['total'] }}
        </strong>

        <small>
            All enquiries
        </small>

    </div>


    <div class="lead-stat-card">

        <span>New</span>

        <strong>
            {{ $stats['new'] }}
        </strong>

        <small>
            Need attention
        </small>

    </div>


    <div class="lead-stat-card">

        <span>Today's Follow-ups</span>

        <strong>
            {{ $stats['followups'] }}
        </strong>

        <small>
            Scheduled today
        </small>

    </div>


    <div class="lead-stat-card">

        <span>Won</span>

        <strong>
            {{ $stats['won'] }}
        </strong>

        <small>
            Converted opportunities
        </small>

    </div>

</div>


{{-- FILTER CARD --}}

<div class="card lead-filter-card">

    <form
        method="GET"
        action="{{ route('admin.leads.index') }}"
        class="lead-filters"
    >

        <div class="filter-search">

            <svg viewBox="0 0 24 24">
                <circle
                    cx="11"
                    cy="11"
                    r="7"
                />
                <path d="m20 20-4-4"/>
            </svg>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search lead, business, mobile..."
            >

        </div>


        <select
            name="status"
            class="filter-control"
        >

            <option value="">
                All Status
            </option>

            @foreach(\App\Models\Lead::STATUSES as $status)

                <option
                    value="{{ $status }}"
                    @selected(request('status') === $status)
                >
                    {{ ucfirst($status) }}
                </option>

            @endforeach

        </select>


        <select
            name="assigned_to"
            class="filter-control"
        >

            <option value="">
                All Executives
            </option>

            @foreach($salesUsers as $salesUser)

                <option
                    value="{{ $salesUser->id }}"
                    @selected(
                        (string) request('assigned_to')
                        ===
                        (string) $salesUser->id
                    )
                >
                    {{ $salesUser->name }}
                </option>

            @endforeach

        </select>


        <select
            name="source"
            class="filter-control"
        >

            <option value="">
                All Sources
            </option>

            @foreach($sources as $source)

                <option
                    value="{{ $source }}"
                    @selected(request('source') === $source)
                >
                    {{ $source }}
                </option>

            @endforeach

        </select>


        <button
            type="submit"
            class="btn btn-secondary"
        >
            Filter
        </button>


        @if(request()->hasAny([
            'search',
            'status',
            'assigned_to',
            'source',
            'business_type',
            'followup_from',
            'followup_to',
        ]))

            <a
                href="{{ route('admin.leads.index') }}"
                class="filter-reset"
            >
                Reset
            </a>

        @endif

    </form>

</div>


{{-- TABLE --}}

<div class="card lead-table-card">

    <div class="table-header">

        <div>

            <h3>
                All Leads
            </h3>

            <span>
                {{ $leads->total() }} records
            </span>

        </div>

    </div>


    <div class="lead-table-wrapper">

        <table class="lead-table">

            <thead>

                <tr>

                    <th>Lead</th>

                    <th>Business</th>

                    <th>Contact</th>

                    <th>Requirement</th>

                    <th>Assigned</th>

                    <th>Status</th>

                    <th>Follow-up</th>

                    <th></th>

                </tr>

            </thead>


            <tbody>

                @forelse($leads as $lead)

                    <tr>

                        <td>

                            <a
                                href="{{ route(
                                    'admin.leads.show',
                                    $lead
                                ) }}"
                                class="lead-code"
                            >
                                {{ $lead->lead_code }}
                            </a>

                            <small>
                                {{ $lead->created_at->format('d M Y') }}
                            </small>

                        </td>


                        <td>

                            <strong class="table-business">
                                {{ $lead->business_name }}
                            </strong>

                            <small>
                                {{ $lead->business_type ?: '—' }}
                            </small>

                        </td>


                        <td>

                            <strong>
                                {{ $lead->contact_name ?: '—' }}
                            </strong>

                            <small>
                                {{ $lead->mobile ?: $lead->email ?: '—' }}
                            </small>

                        </td>


                        <td>

                            @if($lead->estimated_quantity)

                                <strong>
                                    {{ number_format(
                                        $lead->estimated_quantity
                                    ) }}
                                </strong>

                                <small>
                                    {{ $lead->quantity_unit }}
                                    @if($lead->order_frequency)
                                        / {{ $lead->order_frequency }}
                                    @endif
                                </small>

                            @else

                                <span class="text-muted">
                                    —
                                </span>

                            @endif

                        </td>


                        <td>

                            @if($lead->assignedUser)

                                <span class="assigned-user">
                                    {{ $lead->assignedUser->name }}
                                </span>

                            @else

                                <span class="unassigned">
                                    Unassigned
                                </span>

                            @endif

                        </td>


                        <td>

                            <span
                                class="lead-status status-{{ $lead->status }}"
                            >
                                {{ $lead->status_label }}
                            </span>

                        </td>


                        <td>

                            @if($lead->next_followup_at)

                                <span class="
                                    followup-date
                                    {{
                                        $lead->next_followup_at->isPast()
                                        && !in_array(
                                            $lead->status,
                                            ['won','lost']
                                        )
                                        ? 'overdue'
                                        : ''
                                    }}
                                ">

                                    {{
                                        $lead->next_followup_at
                                            ->format('d M, h:i A')
                                    }}

                                </span>

                            @else

                                <span class="text-muted">
                                    —
                                </span>

                            @endif

                        </td>


                        <td>

                            <a
                                href="{{ route(
                                    'admin.leads.show',
                                    $lead
                                ) }}"
                                class="table-action"
                            >
                                View →
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="table-empty"
                        >

                            <div class="empty-icon">
                                ◇
                            </div>

                            <strong>
                                No leads found
                            </strong>

                            <span>
                                Try changing your filters or create a new lead.
                            </span>

                            <a
                                href="{{ route('admin.leads.create') }}"
                                class="btn btn-primary"
                            >
                                + Create Lead
                            </a>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @if($leads->hasPages())

        <div class="table-pagination">

            {{ $leads->links() }}

        </div>

    @endif

</div>

@endsection