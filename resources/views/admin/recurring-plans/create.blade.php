@extends('layouts.admin')
@section('title', 'New Recurring Plan · JALVAN ERP')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/recurring-orders.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('js/recurring-orders.js') }}"></script>
@endpush
@section('content')
<div class="recurring-page">
    <div class="recurring-page__head">
        <div>
            <div class="recurring-page__eyebrow">Orders · Automation</div>
            <h1 class="recurring-page__title">New Recurring Plan</h1>
            <p class="recurring-page__subtitle">Create a repeat bottle-supply schedule for a customer.</p>
        </div>
        <a href="{{ route('admin.recurring-plans.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.recurring-plans.store') }}">
        @csrf
        @include('admin.recurring-plans._form', ['p' => null])
    </form>
</div>
@endsection
