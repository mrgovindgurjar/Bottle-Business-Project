@extends('layouts.admin')
@section('title','New Purchase')
@section('page_title','Purchases')
@section('breadcrumb','Procurement / Purchases / New')
@push('styles')<link rel="stylesheet" href="{{ asset('css/supplier-purchase.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/purchase.js') }}"></script>@endpush
@section('content')<div class="sp-shell"><div class="sp-head"><div><div class="sp-kicker">PROCUREMENT</div><h1>New Purchase</h1><p>Record supplier purchases and receive stock directly into inventory.</p></div></div><form method="POST" action="{{ route('admin.purchases.store') }}">@csrf @include('admin.purchases._form')</form></div>@endsection
