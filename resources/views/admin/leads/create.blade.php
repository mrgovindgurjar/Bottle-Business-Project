@extends('layouts.admin')

@section('title', 'Create Lead')
@section('page_title', 'Create Lead')

@section('breadcrumb')
    CRM / Leads / New Lead
@endsection

@section('content')

<div class="page-header">

    <div>

        <h1 class="page-title">
            Create Lead
        </h1>

        <div class="page-subtitle">
            Add a new business opportunity to your sales pipeline.
        </div>

    </div>

</div>


<div class="card lead-form-card">

    <form
        method="POST"
        action="{{ route('admin.leads.store') }}"
    >

        @include(
            'admin.leads._form'
        )

    </form>

</div>

@endsection