@extends('layouts.admin')
@section('title','Expense '.$expense->expense_number) @section('page_title','Expense '.$expense->expense_number)
@push('styles')<link rel="stylesheet" href="{{ asset('css/income-expenses.css') }}">@endpush
@section('content')

<div class="fin-wrap"><div class="fin-card fin-detail"><div class="fin-detail-head"><div><span class="fin-label">Expense</span><h1>{{ $expense->expense_number }}</h1></div><strong class="expense">₹{{ number_format($expense->amount,2) }}</strong></div><div class="fin-detail-grid"><div><span>Date</span><b>{{ $expense->expense_date?->format('d M Y') }}</b></div><div><span>Category</span><b>{{ $expense->category?->name }}</b></div><div><span>Method</span><b>{{ $expense->payment_method }}</b></div><div><span>Status</span><b>{{ ucfirst($expense->status) }}</b></div><div><span>Supplier</span><b>{{ $expense->supplier?->business_name ?: '—' }}</b></div><div><span>Description</span><b>{{ $expense->description }}</b></div><div><span>Reference</span><b>{{ $expense->reference_number ?: '—' }}</b></div></div><div class="fin-submit"><a class="fin-btn fin-btn-out" href="{{ route('admin.income-expenses.edit-expense',$expense) }}">Edit</a>@if($expense->status!=='cancelled')<form method="POST" action="{{ route('admin.income-expenses.cancel-expense',$expense) }}" onsubmit="return confirm('Cancel this expense entry?')">@csrf<input type="hidden" name="reason" value="Cancelled by admin"><button class="danger">Cancel</button></form>@endif<a href="{{ route('admin.income-expenses.index') }}">Back</a></div></div></div>
@endsection

