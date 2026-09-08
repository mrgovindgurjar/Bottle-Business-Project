@extends('layouts.admin')

@section('title', 'Edit Lead')
@section('page_title', 'Edit Lead')

@section('breadcrumb')
    CRM / Leads / {{ $lead->lead_code }} / Edit
@endsection

@section('content')

<div class="page-header">

    <div>

        <h1 class="page-title">
            Edit Lead
        </h1>

        <div class="page-subtitle">
            Update {{ $lead->business_name }} information.
        </div>

    </div>

</div>


<div class="card lead-form-card">

    <form
        method="POST"
        action="{{ route(
            'admin.leads.update',
            $lead
        ) }}"
    >

        @method('PUT')

        @include(
            'admin.leads._form'
        )

    </form>

</div>

@endsection