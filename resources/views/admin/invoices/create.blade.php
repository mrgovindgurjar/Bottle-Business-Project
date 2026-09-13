@extends('layouts.admin')
@section('content')<div class="invoice-page"><div class="invoice-head"><div><h1>New Invoice</h1><p>Create a customer invoice with server-side totals.</p></div></div>@include('admin.invoices._form',['invoice'=>null,'order'=>$order??null])</div>@endsection
@push('styles')<link rel="stylesheet" href="{{asset('css/invoice.css')}}">@endpush
@push('scripts')<script src="{{asset('js/invoice.js')}}"></script>@endpush
