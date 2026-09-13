@extends('layouts.admin')
@section('title','Edit '.$batch->batch_number)
@section('page_title','Batch')
@section('breadcrumb','Batches / '.$batch->batch_number)
@push('styles')<link rel="stylesheet" href="{{ asset('css/batch.css') }}">@endpush
@section('content')
<div class="bt-shell"><div class="bt-head"><div><div class="bt-kicker">BATCH CONTROL</div><h1>{{ $batch->batch_number }}</h1><p>{{ $batch->product?->name }} · {{ $batch->customer?->business_name ?? 'General Stock' }}</p></div><a class="bt-btn" href="{{ route('admin.batches.show',$batch) }}">← Back</a></div>
<div class="bt-card bt-form-card"><form method="POST" action="{{ route('admin.batches.update',$batch) }}">@csrf @method('PUT')
<div class="bt-source-grid"><label>Manufacturing Date<input type="date" name="manufacturing_date" value="{{ old('manufacturing_date',$batch->manufacturing_date?->format('Y-m-d')) }}" required></label><label>Expiry Date<input type="date" name="expiry_date" value="{{ old('expiry_date',$batch->expiry_date?->format('Y-m-d')) }}"></label><label>Quality Status<select name="quality_status">@foreach(['pending','passed','failed','hold'] as $s)<option value="{{ $s }}" @selected(old('quality_status',$batch->quality_status)===$s)>{{ ucfirst($s) }}</option>@endforeach</select></label></div>
<label>Quality Notes<textarea name="quality_notes" rows="4">{{ old('quality_notes',$batch->quality_notes) }}</textarea></label><label>Batch Notes<textarea name="notes" rows="4">{{ old('notes',$batch->notes) }}</textarea></label><div class="bt-form-actions"><a class="bt-btn" href="{{ route('admin.batches.show',$batch) }}">Cancel</a><button class="bt-btn bt-primary">Save Changes</button></div>
</form></div></div>
@endsection
