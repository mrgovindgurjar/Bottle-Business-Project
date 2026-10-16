@extends('layouts.admin')
@section('title', 'Edit Recurring Plan · JALVAN ERP')
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
            <h1 class="recurring-page__title">Edit Recurring Plan</h1>
            <p class="recurring-page__subtitle">{{ $p->plan_number }} · {{ $p->name }}</p>
        </div>
        <a href="{{ route('admin.recurring-plans.show', $p) }}" class="btn btn-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.recurring-plans.update', $p) }}">
        @csrf
        @method('PUT')
        @include('admin.recurring-plans._form')
    </form>
</div>
@endsection
