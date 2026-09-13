@extends('layouts.admin')
@section('title','Edit '.$purchase->purchase_number)
@section('page_title','Purchases')
@section('breadcrumb','Procurement / Purchases / '.$purchase->purchase_number)
@push('styles')<link rel="stylesheet" href="{{ asset('css/supplier-purchase.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/purchase.js') }}"></script>@endpush
@section('content')<div class="sp-shell"><div class="sp-head"><div><div class="sp-kicker">PURCHASE {{ $purchase->purchase_number }}</div><h1>Edit Purchase</h1><p>Update purchase details before stock is received.</p></div></div><form method="POST" action="{{ route('admin.purchases.update',$purchase) }}">@csrf @method('PUT') @include('admin.purchases._form')</form></div>@endsection
