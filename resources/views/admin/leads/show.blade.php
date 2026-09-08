@extends('layouts.admin')

@section('title', $lead->business_name)
@section('page_title', 'Lead Details')

@section('breadcrumb')
    CRM / Leads / {{ $lead->lead_code }}
@endsection

@section('content')

<div class="lead-detail-header">

    <div class="lead-detail-title">

        <div class="lead-detail-avatar">
            {{ strtoupper(
                substr(
                    $lead->business_name,
                    0,
                    1
                )
            ) }}
        </div>

        <div>

            <div class="lead-code-large">
                {{ $lead->lead_code }}
            </div>

            <h1>
                {{ $lead->business_name }}
            </h1>

            <span>
                {{ $lead->business_type ?: 'Business Lead' }}
            </span>

        </div>

    </div>


    <div class="lead-detail-actions">

        <a
            href="{{ route(
                'admin.leads.edit',
                $lead
            ) }}"
            class="btn btn-secondary"
        >
            Edit
        </a>

        @if(!$lead->converted_customer_id)

            <form
                method="POST"
                action="{{ route(
                    'admin.leads.convert',
                    $lead
                ) }}"
                class="inline-form"
                data-confirm="Convert this lead into a customer?"
            >

                @csrf

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Convert to Customer
                </button>

            </form>

        @endif

    </div>

</div>


{{-- CUSTOMER CREDENTIALS --}}

@if(session('customer_credentials'))

    <div class="credential-alert">

        <div>

            <strong>
                Customer account created
            </strong>

            <span>
                Save these credentials securely. The password is shown only once.
            </span>

        </div>

        <div class="credential-box">

            <div>
                <small>Login</small>
                <strong>
                    {{ session(
                        'customer_credentials.email'
                    ) }}
                </strong>
            </div>

            <div>
                <small>Password</small>
                <strong>
                    {{ session(
                        'customer_credentials.password'
                    ) }}
                </strong>
            </div>

        </div>

    </div>

@endif


{{-- SUMMARY --}}

<div class="lead-detail-grid">

    <div class="lead-detail-main">


        {{-- OVERVIEW --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Lead Overview
                    </h3>

                    <span>
                        Current opportunity information
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

            </div>


            <div class="detail-info-grid">

                <div class="detail-item">

                    <small>
                        Contact Person
                    </small>

                    <strong>
                        {{ $lead->contact_name ?: '—' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Mobile
                    </small>

                    <strong>
                        {{ $lead->mobile ?: '—' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Email
                    </small>

                    <strong>
                        {{ $lead->email ?: '—' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Source
                    </small>

                    <strong>
                        {{ $lead->source ?: '—' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Estimated Quantity
                    </small>

                    <strong>

                        @if($lead->estimated_quantity)

                            {{ number_format(
                                $lead->estimated_quantity
                            ) }}

                            {{ $lead->quantity_unit }}

                        @else

                            —

                        @endif

                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Frequency
                    </small>

                    <strong>
                        {{ $lead->order_frequency ?: '—' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Estimated Value
                    </small>

                    <strong>
                        @if($lead->estimated_value)
                            ₹{{ number_format(
                                $lead->estimated_value,
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </strong>

                </div>


                <div class="detail-item">

                    <small>
                        Assigned To
                    </small>

                    <strong>
                        {{ $lead->assignedUser?->name ?: 'Unassigned' }}
                    </strong>

                </div>

            </div>


            @if($lead->requirement)

                <div class="detail-description">

                    <small>
                        Requirement
                    </small>

                    <p>
                        {{ $lead->requirement }}
                    </p>

                </div>

            @endif

        </div>


        {{-- STATUS FLOW --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Sales Pipeline
                    </h3>

                    <span>
                        Move this opportunity through the sales process.
                    </span>

                </div>

            </div>


            <div class="lead-pipeline">

                @foreach(\App\Models\Lead::STATUSES as $status)

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.leads.status',
                            $lead
                        ) }}"
                        class="pipeline-step-form"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="status"
                            value="{{ $status }}"
                        >

                        <button
                            type="submit"
                            class="
                                pipeline-step
                                {{
                                    $lead->status === $status
                                        ? 'active'
                                        : ''
                                }}
                            "
                        >

                            <span>
                                {{ ucfirst($status) }}
                            </span>

                        </button>

                    </form>

                @endforeach

            </div>

        </div>


        {{-- ACTIVITY TIMELINE --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Activity Timeline
                    </h3>

                    <span>
                        Complete history of sales interactions.
                    </span>

                </div>

            </div>


            <div class="activity-timeline">

                @forelse(
                    $lead->activities
                    as $activity
                )

                    <div class="activity-item">

                        <div
                            class="
                                activity-icon
                                activity-{{ $activity->type }}
                            "
                        >
                            {{ strtoupper(
                                substr(
                                    $activity->type,
                                    0,
                                    1
                                )
                            ) }}
                        </div>


                        <div class="activity-content">

                            <div class="activity-top">

                                <strong>
                                    {{ $activity->subject }}
                                </strong>

                                <span>
                                    {{
                                        $activity->activity_at
                                            ->format(
                                                'd M Y, h:i A'
                                            )
                                    }}
                                </span>

                            </div>


                            <p>
                                {{ $activity->description }}
                            </p>


                            <small>
                                {{
                                    $activity->user?->name
                                    ?: 'System'
                                }}

                                ·

                                {{ ucfirst(
                                    $activity->type
                                ) }}
                            </small>


                            @if(
                                $activity->next_followup_at
                            )

                                <div class="activity-followup">

                                    Next follow-up:
                                    {{
                                        $activity
                                            ->next_followup_at
                                            ->format(
                                                'd M Y, h:i A'
                                            )
                                    }}

                                </div>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="timeline-empty">

                        No activity recorded yet.

                    </div>

                @endforelse

            </div>

        </div>


        {{-- ADD ACTIVITY --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Add Activity
                    </h3>

                    <span>
                        Record your next sales interaction.
                    </span>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route(
                    'admin.leads.activity',
                    $lead
                ) }}"
                class="activity-form"
            >

                @csrf


                <div class="form-grid-2">

                    <div class="form-group">

                        <label class="form-label">
                            Activity Type
                        </label>

                        <select
                            name="type"
                            class="form-control"
                            required
                        >

                            @foreach(
                                \App\Models\Lead::ACTIVITY_TYPES
                                as $type
                            )

                                <option
                                    value="{{ $type }}"
                                >
                                    {{ ucfirst(
                                        $type
                                    ) }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="form-group">

                        <label class="form-label">
                            Activity Date
                        </label>

                        <input
                            type="datetime-local"
                            name="activity_at"
                            class="form-control"
                            value="{{ now()->format(
                                'Y-m-d\TH:i'
                            ) }}"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label class="form-label">
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        class="form-control"
                        placeholder="e.g. Discussed 750ml bottle requirement"
                    >

                </div>


                <div class="form-group">

                    <label class="form-label">
                        Notes
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="What happened during the interaction?"
                        required
                    ></textarea>

                </div>


                <div class="form-group">

                    <label class="form-label">
                        Next Follow-up
                    </label>

                    <input
                        type="datetime-local"
                        name="next_followup_at"
                        class="form-control"
                    >

                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add Activity
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- RIGHT SIDEBAR --}}

    <aside class="lead-detail-sidebar">


        {{-- QUICK ACTIONS --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Quick Actions
                    </h3>

                </div>

            </div>


            <div class="lead-quick-actions">

                <a
                    href="tel:{{ $lead->mobile }}"
                    class="
                        quick-action-button
                        {{
                            $lead->mobile
                                ? ''
                                : 'disabled'
                        }}
                    "
                >
                    Call Lead
                </a>


                <a
                    href="https://wa.me/{{ preg_replace(
                        '/[^0-9]/',
                        '',
                        $lead->mobile ?? ''
                    ) }}"
                    target="_blank"
                    class="
                        quick-action-button
                        {{
                            $lead->mobile
                                ? ''
                                : 'disabled'
                        }}
                    "
                >
                    WhatsApp
                </a>


                <a
                    href="mailto:{{ $lead->email }}"
                    class="
                        quick-action-button
                        {{
                            $lead->email
                                ? ''
                                : 'disabled'
                        }}
                "
                >
                    Email
                </a>


                @if(!$lead->converted_customer_id)

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.leads.convert',
                            $lead
                        ) }}"
                        data-confirm="Convert this lead into a customer?"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="quick-action-button primary"
                        >
                            Convert to Customer
                        </button>

                    </form>

                @else

                    <a
                        href="#"
                        class="quick-action-button primary"
                    >
                        Customer Created
                    </a>

                @endif

            </div>

        </div>


        {{-- FOLLOW UP --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Follow-up
                    </h3>

                </div>

            </div>


            <div class="followup-box">

                @if($lead->next_followup_at)

                    <span>
                        Next follow-up
                    </span>

                    <strong>
                        {{
                            $lead->next_followup_at
                                ->format('d M Y')
                        }}
                    </strong>

                    <small>
                        {{
                            $lead->next_followup_at
                                ->format('h:i A')
                        }}
                    </small>

                @else

                    <span>
                        No follow-up scheduled
                    </span>

                @endif

            </div>

        </div>


        {{-- ADDRESS --}}

        <div class="card lead-detail-card">

            <div class="detail-card-header">

                <div>

                    <h3>
                        Location
                    </h3>

                </div>

            </div>


            <div class="location-info">

                @if($lead->address)

                    <p>
                        {{ $lead->address }}
                    </p>

                @endif

                <strong>

                    {{ $lead->city }}

                    @if($lead->state)
                        , {{ $lead->state }}
                    @endif

                    @if($lead->pincode)
                        - {{ $lead->pincode }}
                    @endif

                </strong>

            </div>

        </div>

    </aside>

</div>

@endsection