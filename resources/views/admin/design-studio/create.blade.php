@extends('layouts.admin')
@section('title','New Design — JALVAN ERP')
@section('page_title','New Design')
@section('breadcrumb') CRM / Design Studio / New Design @endsection
@push('styles')    
    <link rel="stylesheet" href="{{ asset('css/design-studio.css') }}">
@endpush
@push('scripts')    
    <script src="{{ asset('js/design-studio.js') }}"></script>
@endpush

@section('content')
<div class="ds-shell ds-create-shell">
    <div class="ds-page-head"><div><div class="ds-kicker">NEW CREATIVE PROJECT</div><h1>Start a branded bottle design</h1><p>Set the business context first. The visual studio opens after the project is created.</p></div><a class="btn btn-secondary" href="{{ route('admin.designs.index') }}">← Back</a></div>
    <form method="POST" action="{{ route('admin.designs.store') }}" class="card ds-setup-card">@csrf
        <div class="ds-setup-grid">
            <section><div class="ds-section-head"><span>01</span><div><h3>Project identity</h3><p>How this artwork will appear in your ERP.</p></div></div><div class="ds-form-grid"><label>Project title *<input name="title" value="{{ old('title') }}" placeholder="e.g. Taste of India — 1L Bottle" required></label><label>Design type *<select name="design_type">@foreach(['restaurant','hotel','cafe','event','corporate','other'] as $t)<option value="{{ $t }}" @selected(old('design_type')===$t)>{{ ucfirst($t) }}</option>@endforeach</select></label><label>Customer *<select name="customer_id" required><option value="">Select customer</option>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>{{ $c->business_name }} — {{ $c->customer_code }}</option>@endforeach</select></label><label>Product<select name="product_id"><option value="">Select bottle product</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} · {{ $p->bottle_size_ml }}ml · {{ $p->sku }}</option>@endforeach</select></label></div></section>
            <section><div class="ds-section-head"><span>02</span><div><h3>Creative brief</h3><p>Give production/design team enough context.</p></div></div><label>Brief<textarea name="brief" rows="8" placeholder="Brand mood, logo usage, menu QR, phone, address, offers, mandatory text...">{{ old('brief') }}</textarea></label><div class="ds-form-grid"><label>Target delivery date<input type="date" name="due_date" value="{{ old('due_date') }}"></label><div class="ds-brief-tip"><b>Studio includes</b><span>Front + back label</span><span>Logo upload</span><span>QR target</span><span>Live bottle preview</span><span>Version history</span></div></div></section>
        </div>
        <div class="ds-setup-footer"><span>After creation you can visually build and save the first design version.</span><button class="btn btn-primary" type="submit">Open Design Studio →</button></div>
    </form>
</div>
@endsection
