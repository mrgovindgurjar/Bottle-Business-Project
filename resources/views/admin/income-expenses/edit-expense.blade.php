@extends('layouts.admin')
@section('title','Edit Expense') @section('page_title','Edit Expense')

@push('styles')<link rel="stylesheet" href="{{ asset('css/income-expenses.css') }}">@endpush
@section('content')

<div class="fin-wrap"><div class="fin-card fin-form-card"><h1>Edit Expense {{ $expense->expense_number }}</h1><form method="POST" action="{{ route('admin.income-expenses.update-expense',$expense) }}">@csrf @method('PUT') @include('admin.income-expenses._form-expense')</form></div></div>
@endsection

