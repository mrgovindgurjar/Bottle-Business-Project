@extends('layouts.admin')
@section('title','Design Studio — '.$design->title)
@section('page_title','Design Studio')
@section('breadcrumb') CRM / Design Studio / {{ $design->design_code }} @endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/design-studio.css') }}">
@endpush

@section('content')
@php
    $data = $version?->design_data ?? [];
    $jalvanDesignPayload = [
        'design' => $design->only(['id','design_code','status','design_type']),
        'version' => $version?->only(['id','version_no','name','design_data','logo_path','front_artwork_path','back_artwork_path','status','change_note']),
    ];
@endphp

<div class="ds3" id="designStudio"
    data-save-url="{{ route('admin.designs.save',[$design,$version]) }}"
    data-preview-url="{{ route('admin.designs.show',$design) }}"
    data-new-version-url="{{ route('admin.designs.new-version',[$design,$version]) }}"
    data-logo-url="{{ route('admin.designs.logo',[$design,$version]) }}"
    data-artwork-url="{{ route('admin.designs.artwork',[$design,$version]) }}">

    <header class="ds3-head">
        <div class="ds3-head-main">
            <a class="ds3-back" href="{{ route('admin.designs.show',$design) }}">←</a>
            <div>
                <div class="ds3-eyebrow">DESIGN STUDIO · {{ $design->design_code }} · V{{ $version?->version_no ?? 1 }}</div>
                <h1>{{ $design->title }}</h1>
                <p>{{ $design->customer->business_name }} @if($design->product) · {{ $design->product->name }} · {{ $design->product->bottle_size_ml }}ml @endif</p>
            </div>
        </div>
        <div class="ds3-head-actions">
            <span class="ds3-save-state" id="saveState">All changes saved</span>
            <button class="ds3-btn ds3-btn-ghost" type="button" id="undoBtn" title="Undo (Ctrl+Z)">↶</button>
            <button class="ds3-btn ds3-btn-ghost" type="button" id="redoBtn" title="Redo (Ctrl+Y)">↷</button>
            <button class="ds3-btn ds3-btn-ghost" type="button" id="previewBtn">Preview</button>
            <button class="ds3-btn ds3-btn-ghost" type="button" id="newVersionBtn">＋ New Version</button>
            <button class="ds3-btn ds3-btn-primary" type="button" id="saveDesignBtn">Save Draft</button>
        </div>
    </header>

    <div class="ds3-layout">
        <aside class="ds3-left">
            <div class="ds3-tabs" role="tablist">
                <button class="active" data-panel="elements">Elements</button>
                <button data-panel="style">Style</button>
                <button data-panel="assets">Assets</button>
                <button data-panel="print">Print</button>
            </div>

            <div class="ds3-left-scroll">
                <section class="ds3-left-panel active" data-panel-view="elements">
                    <div class="ds3-section-title">Add to label <small>Build your artwork</small></div>
                    <div class="ds3-tool-grid">
                        <button data-add="text"><span>T</span><b>Text</b><small>Heading / copy</small></button>
                        <button data-add="qr"><span>⌗</span><b>QR Code</b><small>Menu / offers</small></button>
                        <button data-add="shape"><span>◇</span><b>Shape</b><small>Accent block</small></button>
                        <button data-add="logo"><span>◉</span><b>Logo</b><small>Brand mark</small></button>
                        <button data-add="divider"><span>—</span><b>Divider</b><small>Visual separator</small></button>
                        <button data-add="artwork"><span>▧</span><b>Artwork</b><small>Uploaded artwork</small></button>
                    </div>
                    <div class="ds3-section-title ds3-space">Layers <small>Top layer renders last</small></div>
                    <div class="ds3-layers" id="layersList"></div>
                    <div class="ds3-layer-actions">
                        <button type="button" id="layerUp">↑ Up</button><button type="button" id="layerDown">↓ Down</button>
                        <button type="button" id="duplicateElement">Duplicate</button><button type="button" id="deleteElement">Delete</button>
                    </div>
                </section>

                <section class="ds3-left-panel" data-panel-view="style">
                    <div class="ds3-section-title">Label style <small>Applied to current side</small></div>
                    <div class="ds3-color-pair">
                        <label>Background<input type="color" id="bgColor"></label>
                        <label>Accent<input type="color" id="accentColor"></label>
                    </div>
                    <label class="ds3-field">Font family<select id="fontFamily"><option>Inter</option><option>Montserrat</option><option>Arial</option><option>Georgia</option><option>Times New Roman</option></select></label>
                    <div class="ds3-section-title ds3-space">Presets <small>One-click starting points</small></div>
                    <div class="ds3-presets">
                        <button data-bg="#ffffff" data-accent="#111827" data-font="Inter">Minimal</button>
                        <button data-bg="#fff8ef" data-accent="#a32d22" data-font="Georgia">Restaurant</button>
                        <button data-bg="#111827" data-accent="#ffffff" data-font="Inter">Luxury</button>
                        <button data-bg="#eefaff" data-accent="#078fca" data-font="Montserrat">Fresh</button>
                    </div>
                    <div class="ds3-field-note">Use the inspector on the right for selected element typography, size, position and rotation.</div>
                </section>

                <section class="ds3-left-panel" data-panel-view="assets">
                    <div class="ds3-section-title">Brand & artwork <small>Version-specific assets</small></div>
                    <label class="ds3-upload"><input type="file" id="logoUpload" accept="image/png,image/jpeg,image/webp,image/svg+xml"><span>＋</span><div><b>Upload brand logo</b><small>PNG, JPG, WEBP, SVG · max 4MB</small></div></label>
                    <label class="ds3-upload"><input type="file" id="frontArtworkUpload" accept="image/png,image/jpeg,image/webp"><span>＋</span><div><b>Front artwork</b><small>PNG, JPG, WEBP · max 6MB</small></div></label>
                    <label class="ds3-upload"><input type="file" id="backArtworkUpload" accept="image/png,image/jpeg,image/webp"><span>＋</span><div><b>Back artwork</b><small>PNG, JPG, WEBP · max 6MB</small></div></label>
                    <div class="ds3-inline-actions"><button type="button" id="showArtwork">Show artwork</button><button type="button" id="hideArtwork">Hide artwork</button></div>
                    <div class="ds3-field-note">Uploaded artwork is a reference/print asset. Editable elements remain the ERP source of truth.</div>
                </section>

                <section class="ds3-left-panel" data-panel-view="print">
                    <div class="ds3-section-title">Print handoff <small>Keep dimensions explicit</small></div>
                    <div class="ds3-two-col">
                        <label class="ds3-field">Width (mm)<input type="number" id="printWidth" min="10" max="1000" step="0.1"></label>
                        <label class="ds3-field">Height (mm)<input type="number" id="printHeight" min="10" max="1000" step="0.1"></label>
                    </div>
                    <label class="ds3-field">Resolution<select id="printDpi"><option value="150">150 DPI · proof</option><option value="300">300 DPI · print</option><option value="600">600 DPI · high quality</option></select></label>
                    <div class="ds3-export-grid"><button type="button" id="exportFront">Export Front PNG</button><button type="button" id="exportBack">Export Back PNG</button><button type="button" id="printLabel">Print Preview</button></div>
                    <div class="ds3-qr-box"><b>QR destination</b><input type="url" id="qrUrl" placeholder="https://example.com/menu"><small>QR is generated live in the browser and the destination is saved with this design version.</small></div>
                    <label class="ds3-field">Version name<input id="versionName" value="{{ $version?->name }}"></label>
                    <label class="ds3-field">Change note<textarea id="changeNote" rows="3" placeholder="What changed in this version?">{{ $version?->change_note }}</textarea></label>
                </section>
            </div>
        </aside>

        <main class="ds3-canvas-wrap">
            <div class="ds3-canvas-toolbar">
                <div class="ds3-side-switch"><button class="active" data-side="front">Front</button><button data-side="back">Back</button></div>
                <div class="ds3-canvas-tools"><button type="button" id="gridBtn">▦ Grid</button><button type="button" id="fitBtn">Fit</button><button type="button" id="zoomOut">−</button><span id="zoomValue">100%</span><button type="button" id="zoomIn">＋</button></div>
            </div>
            <div class="ds3-stage" id="stage">
                <div class="ds3-ruler ds3-ruler-x"></div><div class="ds3-ruler ds3-ruler-y"></div>
                <div class="ds3-bottle-scene">
                    <div class="ds3-orbit o1"></div><div class="ds3-orbit o2"></div>
                    <div class="ds3-bottle">
                        <div class="ds3-cap"></div><div class="ds3-neck"></div>
                        <div class="ds3-body"><div class="ds3-label" id="previewLabel"></div></div><div class="ds3-base"></div>
                    </div>
                    <div class="ds3-ground"></div>
                </div>
                <div class="ds3-stage-tip">Select an element to edit · Drag to move · Arrow keys to nudge · Delete to remove</div>
            </div>
        </main>

        <aside class="ds3-right">
            <div class="ds3-inspector-head"><div><span class="ds3-eyebrow">INSPECTOR</span><h2 id="inspectorTitle">No element selected</h2></div><span class="ds3-live-dot">LIVE</span></div>
            <div id="inspectorEmpty" class="ds3-inspector-empty"><div>✦</div><b>Select an element</b><p>Click text, logo, QR or a shape on the label to edit it.</p></div>
            <div id="inspectorFields" class="ds3-inspector-fields" hidden>
                <div class="ds3-field-group"><div class="ds3-group-title">Content</div><label class="ds3-field">Text<textarea id="elText" rows="3"></textarea></label><label class="ds3-field">Font<select id="elFont"><option>Inter</option><option>Montserrat</option><option>Arial</option><option>Georgia</option><option>Times New Roman</option></select></label></div>
                <div class="ds3-field-group"><div class="ds3-group-title">Typography</div><div class="ds3-two-col"><label class="ds3-field">Size<input type="number" id="elSize" min="6" max="200" step="1"></label><label class="ds3-field">Weight<select id="elWeight"><option value="400">Regular</option><option value="500">Medium</option><option value="600">Semibold</option><option value="700">Bold</option><option value="800">Extra Bold</option></select></label></div><div class="ds3-align-row"><button data-align="left">Left</button><button data-align="center">Center</button><button data-align="right">Right</button></div></div>
                <div class="ds3-field-group"><div class="ds3-group-title">Transform</div><div class="ds3-two-col"><label class="ds3-field">X %<input type="number" id="elX" min="0" max="100" step="0.1"></label><label class="ds3-field">Y %<input type="number" id="elY" min="0" max="100" step="0.1"></label><label class="ds3-field">Width %<input type="number" id="elW" min="1" max="100" step="0.5"></label><label class="ds3-field">Rotation<input type="number" id="elRotate" min="-180" max="180" step="1"></label></div><label class="ds3-field">Opacity<input type="range" id="elOpacity" min="0.1" max="1" step="0.05"></label></div>
                <div class="ds3-field-group"><div class="ds3-group-title">Color</div><div class="ds3-color-pair"><label>Text/Fill<input type="color" id="elColor"></label><label>Border<input type="color" id="elBorder"></label></div></div>
                <div class="ds3-field-group ds3-element-actions"><button type="button" id="alignCenter">Center X</button><button type="button" id="lockElement">Lock</button><button type="button" id="inspectorDuplicate">Duplicate</button><button type="button" id="inspectorDelete" class="danger">Delete</button></div>
            </div>
            <div class="ds3-workflow-card"><div class="ds3-eyebrow">WORKFLOW</div><div class="ds3-flow"><span class="done">Draft</span><i>→</i><span class="{{ in_array($design->status,['in_review','changes_requested','approved'])?'done':'' }}">Review</span><i>→</i><span class="{{ $design->status==='approved'?'done':'' }}">Approved</span><i>→</i><span>Production</span></div><form method="POST" action="{{ route('admin.designs.submit',[$design,$version]) }}">@csrf<button class="ds3-btn ds3-btn-primary ds3-full" {{ in_array($version?->status,['approved','submitted'])?'disabled':'' }}>Submit for Review →</button></form></div>
        </aside>
    </div>
</div>

<form id="designSaveForm" method="POST" action="{{ route('admin.designs.save',[$design,$version]) }}" hidden>@csrf @method('PUT')<input name="design_data" id="designDataInput"><input name="version_name" id="versionNameInput"><input name="change_note" id="changeNoteInput"></form>
<form id="newVersionForm" method="POST" action="{{ route('admin.designs.new-version',[$design,$version]) }}" hidden>@csrf<input name="design_data" id="newVersionDataInput"><input name="version_name" value="New Version"><input name="change_note" id="newVersionNoteInput"></form>

@php $assetBase = asset('storage'); @endphp
<script>
window.JALVAN_DESIGN = {{ \Illuminate\Support\Js::from($jalvanDesignPayload) }};
window.JALVAN_ASSET_BASE = @json($assetBase);
</script>
@endsection

 {{-- <script>
    window.JALVAN_DESIGN = @json([
        'design'  => $design->only(['id', 'design_code', 'status', 'design_type']), 
        'version' => $version?->only(['id', 'version_no', 'name', 'design_data', 'logo_path', 'status', 'change_note'])
    ]);
</script>

<script src="{{ asset('js/design-studio.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/design-studio.css') }}"> --}}


<!-- 1. Define the PHP payload cleanly at the top -->
@php
    $jalvanDesignPayload = [
        'design' => $design->only(['id', 'design_code', 'status', 'design_type']),
        'version' => $version?->only(['id', 'version_no', 'name', 'design_data', 'logo_path', 'status', 'change_note']),
    ];
@endphp

<!-- 2. Safely output the payload to JavaScript using Illuminate\Support\Js -->
<script>
    window.JALVAN_DESIGN = {!! \Illuminate\Support\Js::from($jalvanDesignPayload) !!};
</script>

<!-- 3. Assets placed cleanly without text interruptions -->
<script src="{{ asset('js/design-studio.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/design-studio.css') }}">