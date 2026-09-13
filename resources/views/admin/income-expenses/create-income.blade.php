@extends('layouts.admin')
@section('title','Add Income') @section('page_title','Add Income')
@push('styles')<link rel="stylesheet" href="{{ asset('css/income-expenses.css') }}">@endpush
@section('content')
<div class="fin-wrap"><div class="fin-card fin-form-card"><h1>Add Income</h1><p>Record genuine business income that is not already represented by an existing customer payment/invoice revenue flow.</p><form method="POST" action="{{ route('admin.income-expenses.store-income') }}">@csrf @include('admin.income-expenses._form-income')</form></div></div>
@endsection
