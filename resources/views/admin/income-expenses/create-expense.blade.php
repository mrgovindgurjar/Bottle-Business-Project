@extends('layouts.admin')
@section('title','Add Expense') @section('page_title','Add Expense')
@push('styles')<link rel="stylesheet" href="{{ asset('css/income-expenses.css') }}">@endpush
@section('content')

<div class="fin-wrap"><div class="fin-card fin-form-card"><h1>Add Expense</h1><p>Record operating and business expenses. Supplier purchases can be linked without replacing the Purchase/Payment modules.</p><form method="POST" action="{{ route('admin.income-expenses.store-expense') }}">@csrf @include('admin.income-expenses._form-expense')</form></div></div>

@endsection

