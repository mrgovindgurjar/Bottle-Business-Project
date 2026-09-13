@extends('layouts.admin')
@section('title','Edit Income') @section('page_title','Edit Income')
@push('styles')<link rel="stylesheet" href="{{ asset('css/income-expenses.css') }}">@endpush
@section('content')


<div class="fin-wrap"><div class="fin-card fin-form-card"><h1>Edit Income {{ $income->income_number }}</h1><form method="POST" action="{{ route('admin.income-expenses.update-income',$income) }}">@csrf @method('PUT') @include('admin.income-expenses._form-income')</form></div></div>

@endsection
