@extends('layouts.admin')

@section('title','Design Studio — JALVAN ERP')
@section('page_title','Design Studio')
@section('breadcrumb') CRM / Design Studio @endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/design-studio.css') }}">
@endpush

@section('content')
<div class="ds-shell">
    <div class="ds-page-head">
        <div><div class="ds-kicker">BRAND CREATIVE WORKSPACE</div><h1>Design Studio</h1><p>Create, revise, approve and hand off branded bottle artwork.</p></div>
        <a class="btn btn-primary ds-main-btn" href="{{ route('admin.designs.create') }}">＋ New Design</a>
    </div>

    <div class="ds-stats">
        <div class="ds-stat"><span>Total Projects</span><strong>{{ $stats['total'] }}</strong><small>All design requests</small><i>◇</i></div>
        <div class="ds-stat"><span>Drafts</span><strong>{{ $stats['draft'] }}</strong><small>In creative work</small><i>✎</i></div>
        <div class="ds-stat"><span>In Review</span><strong>{{ $stats['review'] }}</strong><small>Waiting for approval</small><i>◷</i></div>
        <div class="ds-stat"><span>Approved</span><strong>{{ $stats['approved'] }}</strong><small>Ready for production</small><i>✓</i></div>
    </div>

    <div class="ds-toolbar card">
        <form method="GET" class="ds-filter">
            <div class="ds-search"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Search design, customer, code..."></div>
            <select name="status"><option value="">All Status</option>@foreach(['draft','in_review','changes_requested','approved','rejected','archived'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
            <select name="type"><option value="">All Types</option>@foreach(['restaurant','hotel','cafe','event','corporate','other'] as $t)<option value="{{ $t }}" @selected(request('type')===$t)>{{ ucfirst($t) }}</option>@endforeach</select>
            <button class="btn btn-secondary" type="submit">Filter</button>
            @if(request()->hasAny(['q','status','type']))<a href="{{ route('admin.designs.index') }}" class="ds-reset">Reset</a>@endif
        </form>
    </div>

    <div class="card ds-project-card">
        <div class="ds-card-head"><div><div class="ds-kicker">CREATIVE PIPELINE</div><h3>All Design Projects</h3><span>{{ $designs->total() }} records</span></div><a href="{{ route('admin.designs.create') }}" class="ds-inline-action">Start a design →</a></div>
        @if($designs->count())
        <div class="ds-table-wrap"><table class="ds-table"><thead><tr><th>PROJECT</th><th>CUSTOMER</th><th>PRODUCT</th><th>VERSION</th><th>STATUS</th><th>UPDATED</th><th></th></tr></thead><tbody>
        @foreach($designs as $design)
        @php $v=$design->versions->sortByDesc('version_no')->first(); @endphp
        <tr><td><a class="ds-project-name" href="{{ route('admin.designs.show',$design) }}">{{ $design->title }}</a><small>{{ $design->design_code }} · {{ ucfirst($design->design_type) }}</small></td><td><strong>{{ $design->customer->business_name }}</strong><small>{{ $design->customer->customer_code }}</small></td><td>{{ $design->product?->name ?? 'Any product' }}<small>{{ $design->product?->bottle_size_ml ? $design->product->bottle_size_ml.' ml' : '—' }}</small></td><td><span class="ds-version">V{{ $v?->version_no ?? 1 }}</span><small>{{ $v?->name ?? 'Version 1' }}</small></td><td><span class="ds-status ds-{{ $design->status }}"><b></b>{{ ucwords(str_replace('_',' ',$design->status)) }}</span></td><td>{{ $design->updated_at->format('d M, h:i A') }}</td><td><a class="ds-view" href="{{ route('admin.designs.show',$design) }}">View →</a></td></tr>
        @endforeach
        </tbody></table></div><div class="ds-pagination">{{ $designs->links() }}</div>
        @else
        <div class="ds-empty"><div class="ds-empty-icon">✦</div><h3>No design projects yet</h3><p>Start with a customer, bottle and brand brief. You can build both front and back labels in the studio.</p><a class="btn btn-primary" href="{{ route('admin.designs.create') }}">＋ Create first design</a></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
 <script src="{{ asset('js/design-studio.js') }}"></script>
@endpush
