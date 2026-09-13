@extends('layouts.admin')
@section('title','Suppliers')
@section('page_title','Suppliers')
@section('breadcrumb','Procurement / Suppliers')
@push('styles')<link rel="stylesheet" href="{{ asset('css/supplier-purchase.css') }}">@endpush
@section('content')
<div class="sp-shell">
  @if(session('success'))<div class="sp-alert sp-success">{{ session('success') }}</div>@endif
  <div class="sp-head"><div><div class="sp-kicker">PROCUREMENT</div><h1>Suppliers</h1><p>Manage vendors, purchase history and outstanding balances.</p></div><a class="sp-btn sp-primary" href="{{ route('admin.suppliers.create') }}">+ New Supplier</a></div>
  <div class="sp-stats"><div><span>Suppliers</span><b>{{ $suppliers->total() }}</b></div><div><span>Active</span><b>{{ \App\Models\Supplier::where('status','active')->count() }}</b></div><div><span>Purchase Value</span><b>₹{{ number_format((float)\App\Models\Purchase::where('status','!=','cancelled')->sum('grand_total'),2) }}</b></div><div><span>Payables</span><b>₹{{ number_format(max(0,(float)\App\Models\Purchase::where('status','!=','cancelled')->sum('balance_amount')),2) }}</b></div></div>
  <form class="sp-filters" method="GET"><input name="q" value="{{ request('q') }}" placeholder="Search supplier, code, mobile, GSTIN"><select name="status"><option value="">All Status</option>@foreach(['active','inactive','blocked'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select><select name="category"><option value="">All Categories</option>@foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')===$c)>{{ $c }}</option>@endforeach</select><button class="sp-btn sp-primary">Search</button><a class="sp-btn" href="{{ route('admin.suppliers.index') }}">Reset</a></form>
  <div class="sp-card"><div class="sp-card-head"><div><div class="sp-kicker">VENDOR DIRECTORY</div><h2>All Suppliers</h2></div><span>{{ $suppliers->total() }} records</span></div>
  <div class="sp-table-wrap"><table class="sp-table"><thead><tr><th>Supplier</th><th>Contact</th><th>Category</th><th>Terms</th><th>Purchases</th><th>Value</th><th>Status</th><th></th></tr></thead><tbody>
  @forelse($suppliers as $s)<tr><td><strong>{{ $s->business_name }}</strong><small>{{ $s->supplier_code }}</small></td><td>{{ $s->contact_name ?: '—' }}<small>{{ $s->mobile ?: $s->email ?: '—' }}</small></td><td>{{ $s->category ?: '—' }}</td><td>{{ $s->payment_terms_days }} days</td><td>{{ $s->purchases_count }}</td><td>₹{{ number_format((float)$s->purchase_value,2) }}</td><td><span class="sp-status {{ $s->status }}">{{ ucfirst($s->status) }}</span></td><td><a class="sp-link" href="{{ route('admin.suppliers.show',$s) }}">View →</a></td></tr>@empty<tr><td colspan="8"><div class="sp-empty"><b>No suppliers yet</b><span>Add your first vendor to start recording purchases.</span><a class="sp-btn sp-primary" href="{{ route('admin.suppliers.create') }}">+ New Supplier</a></div></td></tr>@endforelse
  </tbody></table></div><div class="sp-pagination">{{ $suppliers->links() }}</div></div>
</div>
@endsection
