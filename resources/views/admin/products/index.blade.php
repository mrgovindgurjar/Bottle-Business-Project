@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Products')
@section('breadcrumb')
Catalog / Products
@endsection

@section('content')
<div class="product-page-shell">
    <div class="page-header product-page-header">
        <div>
            <div class="eyebrow">CATALOG / PRODUCT MASTER</div>
            <h1 class="page-title">Products</h1>
            <div class="page-subtitle">Manage bottle formats, packaging specifications and commercial availability.</div>
        </div>
        @if(auth()->user()->hasPermission('products.create'))
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary product-primary-btn">+ New Product</a>
        @endif
    </div>

    <div class="product-stats">
        <div class="product-stat-card"><span>Total Products</span><strong>{{ $stats['total'] }}</strong><small>All catalog items</small><i>◈</i></div>
        <div class="product-stat-card"><span>Active</span><strong>{{ $stats['active'] }}</strong><small>Available for sale</small><i>✓</i></div>
        <div class="product-stat-card"><span>Bottle Sizes</span><strong>{{ $stats['sizes'] }}</strong><small>Distinct formats</small><i>▱</i></div>
        <div class="product-stat-card"><span>Custom Branding</span><strong>{{ $stats['custom'] }}</strong><small>Brand-ready products</small><i>✦</i></div>
    </div>

    <div class="product-toolbar card">
        <form method="GET" action="{{ route('admin.products.index') }}" class="product-filter-form" data-product-filter-form>
            <div class="product-search-wrap">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input name="search" value="{{ request('search') }}" placeholder="Search product, SKU, material..." autocomplete="off">
            </div>
            <select name="status" class="product-filter-select">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <select name="bottle_size_ml" class="product-filter-select">
                <option value="">All Sizes</option>
                @foreach($sizes as $size)<option value="{{ $size }}" @selected((string)request('bottle_size_ml') === (string)$size)>{{ $size }} ml</option>@endforeach
            </select>
            <select name="bottle_type" class="product-filter-select">
                <option value="">All Bottle Types</option>
                @foreach($types as $type)<option value="{{ $type }}" @selected(request('bottle_type') === $type)>{{ $type }}</option>@endforeach
            </select>
            <button class="btn btn-secondary" type="submit">Filter</button>
            @if(request()->hasAny(['search','status','bottle_size_ml','bottle_type']))
                <a class="product-reset" href="{{ route('admin.products.index') }}">Reset</a>
            @endif
        </form>
    </div>

    <div class="product-catalog-card card">
        <div class="product-card-head">
            <div><div class="eyebrow">PRODUCT MASTER</div><h3>All Products</h3><span>{{ $products->total() }} catalog records</span></div>
            <a href="{{ route('admin.pricing.index') }}" class="product-head-link">Manage pricing →</a>
        </div>

        <div class="product-table-wrap">
            <table class="product-table">
                <thead><tr><th>Product</th><th>Specification</th><th>Packaging</th><th>Pricing</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="product-cell-main">
                                <div class="product-thumb">
                                    @if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}">@else<span>{{ strtoupper(substr($product->name,0,1)) }}</span>@endif
                                </div>
                                <div><a href="{{ route('admin.products.show',$product) }}" class="product-name">{{ $product->name }}</a><small>{{ $product->sku }}</small><em>{{ $product->is_custom_branding ? 'Custom branding ready' : 'Standard product' }}</em></div>
                            </div>
                        </td>
                        <td><strong>{{ number_format($product->bottle_size_ml) }} ml</strong><small>{{ $product->bottle_type ?: '—' }} · {{ $product->material ?: 'Material N/A' }}</small></td>
                        <td><strong>{{ number_format($product->units_per_box) }} / {{ $product->unit }}</strong><small>{{ $product->cap_type ?: 'Cap N/A' }} · {{ $product->label_type ?: 'Label N/A' }}</small></td>
                        <td><strong>{{ $product->activePrices->whereNull('customer_id')->count() }} global</strong><small>{{ $product->activePrices->whereNotNull('customer_id')->count() }} customer-specific</small></td>
                        <td><span class="product-status {{ $product->status === 'active' ? 'is-active' : 'is-inactive' }}"><b></b>{{ ucfirst($product->status) }}</span></td>
                        <td><a href="{{ route('admin.products.show',$product) }}" class="product-view-btn">View →</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="product-empty"><div class="product-empty-icon">◇</div><strong>No products yet</strong><span>Create your first bottle product to start building pricing and order flows.</span>@if(auth()->user()->hasPermission('products.create'))<a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Create Product</a>@endif</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())<div class="product-pagination">{{ $products->links() }}</div>@endif
    </div>
</div>
@endsection
