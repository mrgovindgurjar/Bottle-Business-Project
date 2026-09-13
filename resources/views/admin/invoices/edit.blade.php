@extends('layouts.admin')
@section('content')<div class="invoice-page"><div class="invoice-head"><div><h1>Edit {{$invoice->invoice_number}}</h1></div></div>@include('admin.invoices._form',['invoice'=>$invoice,'order'=>$invoice->order])</div>@endsection
@push('styles')<link rel="stylesheet" href="{{asset('css/invoice.css')}}">@endpush
@push('scripts')<script src="{{asset('js/invoice.js')}}"></script>@endpush
