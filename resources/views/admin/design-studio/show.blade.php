@extends('layouts.admin')
@section('title','Design Preview — '.$design->title)
@section('page_title','Design Preview')
@section('breadcrumb') CRM / Design Studio / {{ $design->design_code }} @endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/design-studio.css') }}">
<style>
.ds4-show{max-width:1500px;margin:0 auto;padding:22px 0 40px}.ds4-hero{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;margin-bottom:18px}.ds4-hero h1{margin:5px 0;font-size:28px;letter-spacing:-.04em}.ds4-hero p{margin:0;color:#7a879c;font-size:11px}.ds4-actions{display:flex;gap:8px;flex-wrap:wrap}.ds4-layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px}.ds4-card{background:#fff;border:1px solid #e6ebf2;border-radius:18px;box-shadow:0 8px 30px rgba(16,24,40,.05);overflow:hidden}.ds4-card-head{padding:18px 20px;border-bottom:1px solid #eef1f5;display:flex;justify-content:space-between;align-items:center}.ds4-card-head h2{font-size:15px;margin:4px 0}.ds4-card-head small{color:#98a2b3}.ds4-preview{background:radial-gradient(circle at 50% 25%,#fff 0,#f2f6fa 55%,#e8eef4 100%);padding:45px;display:flex;justify-content:center;gap:70px;min-height:610px}.ds4-bottle{position:relative;width:230px;height:540px;filter:drop-shadow(0 22px 25px rgba(16,24,40,.16))}.ds4-cap{position:absolute;top:0;left:80px;width:70px;height:43px;border-radius:12px 12px 8px 8px;background:linear-gradient(90deg,#d6dce1,#fff,#d6dce1);border:1px solid #ccd3da}.ds4-neck{position:absolute;top:36px;left:88px;width:54px;height:90px;border-radius:8px 8px 20px 20px;background:linear-gradient(90deg,#c7edf7,#fff,#b9e4ef)}.ds4-body{position:absolute;top:84px;left:15px;width:200px;height:400px;border-radius:45px 45px 38px 38px;background:linear-gradient(90deg,rgba(160,218,235,.48),rgba(255,255,255,.88) 35%,rgba(180,225,236,.5));border:1px solid #d8e4ea;overflow:hidden}.ds4-label{position:absolute;left:12%;top:16%;width:76%;height:68%;border-radius:10px;overflow:hidden;box-shadow:0 8px 20px rgba(16,24,40,.14);border:1px solid rgba(16,24,40,.08)}.ds4-el{position:absolute;display:flex;align-items:center;justify-content:center;line-height:1.12;white-space:pre-wrap;word-break:break-word}.ds4-el img{max-width:100%;max-height:100%;object-fit:contain}.ds4-qr{background:#fff;padding:4px;border-radius:5px}.ds4-art{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.12}.ds4-side{padding:18px;display:flex;flex-direction:column;gap:10px}.ds4-info{padding:13px;border:1px solid #edf0f4;border-radius:12px}.ds4-info span{display:block;color:#98a2b3;font-size:8px;text-transform:uppercase;letter-spacing:.08em}.ds4-info strong{display:block;margin-top:5px;font-size:11px}.ds4-info p,.ds4-info small{color:#667085;font-size:9px;line-height:1.5;margin:6px 0 0}.ds4-version{display:grid;grid-template-columns:40px 1fr;gap:5px;padding:9px 0;border-bottom:1px solid #f0f2f5;font-size:9px}.ds4-comments{margin-top:16px}.ds4-comment{padding:14px 20px;border-bottom:1px solid #f0f2f5;display:flex;gap:10px}.ds4-avatar{width:32px;height:32px;border-radius:10px;background:#f1f4f7;display:grid;place-items:center;font-size:9px;font-weight:800}.ds4-comment strong{font-size:10px}.ds4-comment small{display:block;color:#98a2b3;font-size:8px;margin-top:2px}.ds4-comment p{margin:6px 0 0;font-size:9px;color:#667085}.ds4-comment-form{padding:15px 20px;display:flex;gap:9px}.ds4-comment-form textarea{flex:1;border:1px solid #dfe5ed;border-radius:10px;padding:10px;resize:vertical}.ds4-status{display:inline-flex;gap:6px;align-items:center;padding:6px 9px;border-radius:999px;font-size:8px;font-weight:800;background:#f2f4f7}.ds4-status b{width:5px;height:5px;border-radius:50%;background:currentColor}.ds4-timeline{display:flex;gap:5px;align-items:center;flex-wrap:wrap}.ds4-timeline span{padding:5px 7px;border-radius:7px;background:#f2f4f7;font-size:7px;font-weight:800;color:#98a2b3}.ds4-timeline span.done{background:#ecfdf3;color:#067647}.ds4-print{padding:16px 20px;border-top:1px solid #eef1f5;display:flex;justify-content:space-between;align-items:center;color:#667085;font-size:9px}
@media(max-width:1100px){.ds4-layout{grid-template-columns:1fr}.ds4-side{display:grid;grid-template-columns:repeat(2,1fr)}}@media(max-width:760px){.ds4-hero{flex-direction:column}.ds4-preview{gap:20px;padding:25px;overflow:auto}.ds4-bottle{flex:0 0 200px;transform:scale(.82)}.ds4-side{display:flex}.ds4-actions{width:100%}}
</style>
@endpush
@section('content')

@php $data=$version?->design_data ?? []; $front=$data['front'] ?? []; $back=$data['back'] ?? []; @endphp
<div class="ds4-show" id="designPreview" data-qr="{{ data_get($data,'global.qr_url','') }}">
    <div class="ds4-hero">
        <div><div class="ds-kicker">{{ $design->design_code }} · V{{ $version?->version_no ?? 1 }}</div><h1>{{ $design->title }}</h1><p>{{ $design->customer->business_name }} · {{ $design->product?->name ?? 'Bottle product' }}</p></div>
        <div class="ds4-actions"><a class="btn btn-secondary" href="{{ route('admin.designs.index') }}">← Designs</a><a class="btn btn-primary" href="{{ route('admin.designs.edit',$design) }}">Edit Studio →</a></div>
    </div>
    <div class="ds4-layout">
        <div>
            <section class="ds4-card"><div class="ds4-card-head"><div><div class="ds-kicker">LIVE ARTWORK</div><h2>Production preview</h2><small>Current saved version · V{{ $version?->version_no ?? 1 }}</small></div><span class="ds4-status"><b></b>{{ ucwords(str_replace('_',' ',$design->status)) }}</span></div>
                <div class="ds4-preview">
                    @foreach(['front','back'] as $side)
                    @php $s=$side==='front'?$front:$back; $artPath=$version?->{$side.'_artwork_path'}; @endphp
                    <div class="ds4-bottle"><div class="ds4-cap"></div><div class="ds4-neck"></div><div class="ds4-body"><div class="ds4-label" style="background:{{ $s['background'] ?? '#fff' }};color:{{ $s['accent'] ?? '#111827' }}">
                        @if($artPath)<img class="ds4-art" src="{{ asset('storage/'.$artPath) }}" alt="{{ ucfirst($side) }} artwork">@endif
                        @if(!empty($s['elements']))
                            @foreach($s['elements'] as $e)
                                @php $type=$e['type']??'text'; $style='left:'.($e['x']??50).'%;top:'.($e['y']??50).'%;width:'.($e['w']??40).'%;opacity:'.($e['opacity']??1).';transform:translate(-50%,-50%) rotate('.($e['rotate']??0).'deg) scale('.($type==='logo'?($e['scale']??1):1).');color:'.($e['color']??($s['accent']??'#111827')).';font-size:'.($e['size']??20).'px;font-weight:'.($e['weight']??500).';text-align:'.($e['align']??'center').';font-family:'.($e['font']??'Inter').';z-index:'.($loop->index+2).';'; @endphp
                                @if($type==='logo')<div class="ds4-el" style="{{ $style }}">@if($version?->logo_path)<img src="{{ asset('storage/'.$version->logo_path) }}" alt="Logo">@else<b>J</b>@endif</div>
                                @elseif($type==='qr')<div class="ds4-el ds4-qr" style="{{ $style }}"><div class="ds4-qr-holder" data-url="{{ data_get($data,'global.qr_url','https://jalvan.in') }}"></div></div>
                                @elseif($type==='shape')<div class="ds4-el" style="{{ $style }};height:{{ max(4,(($e['size']??18)/2)) }}%;background:{{ $e['color']??'#111827' }};border:1px solid {{ $e['border']??'#fff' }};border-radius:{{ ($e['shape']??'rounded')==='circle'?'999px':'12px' }}"></div>
                                @elseif($type==='divider')<div class="ds4-el" style="{{ $style }};height:0;border-top:2px solid {{ $e['color']??'#111827' }}"></div>
                                @elseif($type==='artwork')<div class="ds4-el" style="{{ $style }};height:{{ max(10,$e['size']??20) }}%">@if($artPath)<img src="{{ asset('storage/'.$artPath) }}" alt="Artwork">@endif</div>
                                @else<div class="ds4-el" style="{{ $style }}">{{ $e['text']??'Text' }}</div>@endif
                            @endforeach
                        @else
                            <div class="ds4-el" style="left:50%;top:38%;width:85%;transform:translate(-50%,-50%);font-size:17px;font-weight:800">{{ $s['title']??($side==='front'?'PURE TASTE':'OUR MENU') }}</div>
                            <div class="ds4-el" style="left:50%;top:49%;width:85%;transform:translate(-50%,-50%);font-size:8px">{{ $s['subtitle']??'' }}</div>
                            <div class="ds4-el" style="left:50%;top:60%;width:85%;transform:translate(-50%,-50%);font-size:9px;font-weight:700">{{ $s['body']??'' }}</div>
                        @endif
                    </div></div></div>
                    @endforeach
                </div>
                <div class="ds4-print"><span>Front + Back · print handoff</span><span>{{ data_get($data,'global.print_width_mm',70) }} × {{ data_get($data,'global.print_height_mm',160) }} mm · {{ data_get($data,'global.print_dpi',300) }} DPI</span></div>
            </section>
            @if($version)<section class="ds4-card ds4-comments"><div class="ds4-card-head"><div><div class="ds-kicker">COLLABORATION</div><h2>Comments & review</h2></div></div>
                @forelse($version->comments as $comment)<div class="ds4-comment"><span class="ds4-avatar">{{ strtoupper(substr($comment->user?->name ?? 'A',0,1)) }}</span><div><strong>{{ $comment->user?->name ?? 'Admin' }}</strong><small>{{ $comment->created_at->format('d M Y, h:i A') }} · {{ ucfirst($comment->type) }}</small><p>{{ $comment->comment }}</p></div></div>@empty<div style="padding:20px;color:#98a2b3;font-size:9px">No comments on this version yet.</div>@endforelse
                <form method="POST" action="{{ route('admin.designs.comment',[$design,$version]) }}" class="ds4-comment-form">@csrf<textarea name="comment" rows="2" placeholder="Add a design review comment..." required></textarea><button class="btn btn-secondary">Add Comment</button></form>
            </section>@endif
        </div>
        <aside class="ds4-card ds4-side">
            <div class="ds4-info"><span>Customer</span><strong>{{ $design->customer->business_name }}</strong><small>{{ $design->customer->customer_code }}</small></div>
            <div class="ds4-info"><span>Product</span><strong>{{ $design->product?->name ?? 'Any bottle' }}</strong><small>{{ $design->product?->bottle_size_ml ? $design->product->bottle_size_ml.' ml' : 'Product can be selected later' }}</small></div>
            <div class="ds4-info"><span>QR destination</span><strong style="word-break:break-all">{{ data_get($data,'global.qr_url','Not configured') }}</strong></div>
            <div class="ds4-info"><span>Brief</span><p>{{ $design->brief ?: 'No brief added.' }}</p></div>
            <div class="ds4-info"><span>Workflow</span><div class="ds4-timeline"><span class="done">Draft</span><span class="{{ in_array($design->status,['in_review','changes_requested','approved'])?'done':'' }}">Review</span><span class="{{ $design->status==='approved'?'done':'' }}">Approved</span><span>Production</span></div></div>
            <div class="ds4-info"><span>Version history</span>@foreach($design->versions->sortByDesc('version_no') as $v)<div class="ds4-version"><b>V{{ $v->version_no }}</b><span>{{ ucfirst($v->status) }}<br><small>{{ $v->created_at->format('d M Y') }}</small></span></div>@endforeach</div>
            @if($version && $version->status==='submitted')<form method="POST" action="{{ route('admin.designs.approve',[$design,$version]) }}">@csrf<button class="btn btn-primary" style="width:100%">Approve V{{ $version->version_no }} ✓</button></form><form method="POST" action="{{ route('admin.designs.changes',[$design,$version]) }}" style="margin-top:7px">@csrf<textarea name="comment" rows="2" placeholder="Reason for changes..." required style="width:100%;padding:9px;border:1px solid #dfe5ed;border-radius:9px"></textarea><button class="btn btn-secondary" style="width:100%;margin-top:6px">Request Changes</button></form>@endif
        </aside>
    </div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>document.querySelectorAll('.ds4-qr-holder').forEach(function(h){try{new QRCode(h,{text:h.dataset.url||'https://jalvan.in',width:72,height:72,correctLevel:QRCode.CorrectLevel.M});}catch(e){h.textContent='QR';}});</script>

 <script src="{{ asset('js/design-studio.js') }}"></script>

@endpush
@endsection
