@extends('layouts.admin')
@section('title','Create Batch — JALVAN ERP')
@section('page_title','Create Batch')
@section('breadcrumb','Batches / New')
@push('styles')<link rel="stylesheet" href="{{ asset('css/batch.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/batch.js') }}"></script>@endpush
@section('content')
<div class="bt-shell">
  <div class="bt-head"><div><div class="bt-kicker">NEW TRACEABLE LOT</div><h1>Create Batch</h1><p>Create a batch only from completed production output.</p></div><a class="bt-btn" href="{{ route('admin.batches.index') }}">← Batches</a></div>
  <div class="bt-card bt-form-card"><form method="POST" action="{{ route('admin.batches.store') }}">@csrf
    <div class="bt-section"><div class="bt-section-title"><span>01</span><div><h2>Production Source</h2><p>Choose the completed manufacturing job and product line.</p></div></div>
      <label>Completed Production<select name="production_order_id" id="productionSelect" required><option value="">Select production</option>@foreach($productions as $p)<option value="{{ $p->id }}" @selected(old('production_order_id',$production?->id)===$p->id)>{{ $p->production_number }} · {{ $p->customer?->business_name }} · {{ number_format($p->produced_quantity,0) }} produced</option>@endforeach</select></label>
      <div class="bt-source-grid"><label>Product<select name="product_id" id="productSelect" required><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} · {{ $product->sku }} · {{ $product->bottle_size_ml }}ml</option>@endforeach</select></label><label>Production Item ID <input type="number" name="production_order_item_id" value="{{ old('production_order_item_id') }}" placeholder="Optional"></label></div>
      <div class="bt-source-note">The server verifies that the selected production is completed and that the product belongs to the production source.</div>
    </div>
    <div class="bt-section"><div class="bt-section-title"><span>02</span><div><h2>Batch Identity</h2><p>Batch number is generated automatically and cannot collide.</p></div></div>
      <div class="bt-source-grid"><label>Customer<select name="customer_id"><option value="">Use production customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id')==$customer->id)>{{ $customer->business_name }} · {{ $customer->customer_code }}</option>@endforeach</select></label><label>Design ID <input type="number" name="design_id" value="{{ old('design_id') }}" placeholder="Optional Design Studio ID"></label></div>
    </div>
    <div class="bt-section"><div class="bt-section-title"><span>03</span><div><h2>Quantity & Dates</h2><p>Enter the actual accepted output that this batch represents.</p></div></div>
      <div class="bt-source-grid"><label>Produced Quantity<input type="number" name="produced_quantity" step="0.001" min="0.001" value="{{ old('produced_quantity',$production && $production->items->count()===1 ? $production->produced_quantity : '') }}" required></label><label>Rejected Quantity<input type="number" name="rejected_quantity" step="0.001" min="0" value="{{ old('rejected_quantity',0) }}"></label><label>Manufacturing Date<input type="date" name="manufacturing_date" value="{{ old('manufacturing_date',now()->format('Y-m-d')) }}" required></label><label>Expiry Date<input type="date" name="expiry_date" value="{{ old('expiry_date') }}"></label></div>
    </div>
    <div class="bt-section"><div class="bt-section-title"><span>04</span><div><h2>Quality Release</h2><p>A passed batch is released immediately; other states remain controlled.</p></div></div>
      <div class="bt-source-grid"><label>Quality Status<select name="quality_status"><option value="pending">Pending</option><option value="passed" @selected(old('quality_status')==='passed')>Passed</option><option value="failed" @selected(old('quality_status')==='failed')>Failed</option><option value="hold" @selected(old('quality_status')==='hold')>Hold</option></select></label><label>Quality Notes<textarea name="quality_notes" rows="3">{{ old('quality_notes') }}</textarea></label></div>
      <label>Batch Notes<textarea name="notes" rows="4">{{ old('notes') }}</textarea></label>
    </div>
    <div class="bt-form-actions"><a class="bt-btn" href="{{ route('admin.batches.index') }}">Cancel</a><button class="bt-btn bt-primary" type="submit">Create Batch →</button></div>
  </form></div>
</div>
@endsection
