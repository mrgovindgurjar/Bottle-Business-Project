@extends('layouts.admin')
@section('title','Batches — JALVAN ERP')
@section('page_title','Batches')
@section('breadcrumb','Design & Production / Batches')
@push('styles')<link rel="stylesheet" href="{{ asset('css/batch.css') }}">@endpush
@section('content')
<div class="bt-shell">
  <div class="bt-head"><div><div class="bt-kicker">TRACEABILITY CONTROL</div><h1>Batches</h1><p>Track production lots, quality release and customer allocation.</p></div><a class="bt-btn bt-primary" href="{{ route('admin.batches.create') }}">+ New Batch</a></div>
  <div class="bt-stats">
    <div class="bt-stat"><span>Total Batches</span><b>{{ $stats['total'] }}</b><small>All production lots</small></div>
    <div class="bt-stat"><span>Quality Pending</span><b>{{ $stats['quality_pending'] }}</b><small>Awaiting release</small></div>
    <div class="bt-stat"><span>Released</span><b>{{ $stats['released'] }}</b><small>Ready to allocate</small></div>
    <div class="bt-stat"><span>Allocated</span><b>{{ $stats['allocated'] }}</b><small>Partially issued</small></div>
    <div class="bt-stat"><span>Exhausted</span><b>{{ $stats['exhausted'] }}</b><small>No quantity remaining</small></div>
    <div class="bt-stat"><span>Blocked</span><b>{{ $stats['blocked'] }}</b><small>Do not use</small></div>
  </div>
  <form class="bt-filters" method="GET">
    <input name="q" value="{{ request('q') }}" placeholder="Search batch, production, customer, product...">
    <select name="status"><option value="">All Status</option>@foreach(['quality_pending','released','allocated','exhausted','blocked','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
    <select name="quality_status"><option value="">All Quality</option>@foreach(['pending','passed','failed','hold'] as $s)<option value="{{ $s }}" @selected(request('quality_status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
    <select name="customer_id"><option value="">All Customers</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(request('customer_id')==$customer->id)>{{ $customer->business_name }}</option>@endforeach</select>
    <input type="date" name="date" value="{{ request('date') }}">
    <button class="bt-btn bt-primary">Search</button><a class="bt-btn" href="{{ route('admin.batches.index') }}">Reset</a>
  </form>
  <div class="bt-card"><div class="bt-card-head"><div><div class="bt-kicker">BATCH REGISTER</div><h2>All Batches</h2></div><span>{{ $batches->total() }} records</span></div>
    @if($batches->count())
    <div class="bt-table-wrap"><table><thead><tr><th>Batch</th><th>Production</th><th>Product</th><th>Customer</th><th>Produced</th><th>Available</th><th>Quality</th><th>Status</th><th>MFG / Expiry</th><th></th></tr></thead><tbody>
    @foreach($batches as $batch)<tr>
      <td><strong>{{ $batch->batch_number }}</strong><small>{{ $batch->created_at->format('d M Y') }}</small></td>
      <td>{{ $batch->productionOrder?->production_number ?? '—' }}</td>
      <td>{{ $batch->product?->name ?? '—' }}<small>{{ $batch->product?->sku }}</small></td>
      <td>{{ $batch->customer?->business_name ?? 'General Stock' }}</td>
      <td>{{ rtrim(rtrim(number_format($batch->produced_quantity,3),'0'),'.') }}</td>
      <td><strong>{{ rtrim(rtrim(number_format($batch->calculatedAvailableQuantity(),3),'0'),'.') }}</strong></td>
      <td><span class="bt-quality {{ $batch->quality_status }}">{{ ucfirst($batch->quality_status) }}</span></td>
      <td><span class="bt-status {{ $batch->status }}">{{ ucwords(str_replace('_',' ',$batch->status)) }}</span></td>
      <td>{{ $batch->manufacturing_date?->format('d M Y') }}<small>{{ $batch->expiry_date ? 'Exp '.$batch->expiry_date->format('d M Y') : 'No expiry' }}</small></td>
      <td><a class="bt-link" href="{{ route('admin.batches.show',$batch) }}">View →</a></td>
    </tr>@endforeach
    </tbody></table></div><div class="bt-pagination">{{ $batches->links() }}</div>
    @else<div class="bt-empty"><h3>No batches yet</h3><p>Complete a production job and create your first traceable batch.</p><a class="bt-btn bt-primary" href="{{ route('admin.batches.create') }}">+ New Batch</a></div>@endif
  </div>
</div>
@endsection
