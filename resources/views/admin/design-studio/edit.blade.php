@extends('layouts.admin')
@section('title', 'Design Studio — ' . $design->title)


@section('page_title', 'Design Studio')
@section('breadcrumb') CRM / Design Studio / {{ $design->design_code }} @endsection



@section('content')
    @php $data = $version?->design_data ?? [];
        $front = $data['front'] ?? [];
        $back = $data['back'] ?? [];
    $global = $data['global'] ?? []; @endphp
    <div class="ds-studio-shell" id="designStudio" data-save-url="{{ route('admin.designs.save', [$design, $version]) }}"
        data-new-version-url="{{ route('admin.designs.new-version', [$design, $version]) }}">
        <div class="ds-studio-top">
            <div>
                <div class="ds-kicker">{{ $design->design_code }} · V{{ $version?->version_no ?? 1 }}</div>
                <h1>{{ $design->title }}</h1>
                <p>{{ $design->customer->business_name }} @if($design->product) · {{ $design->product->name }} ·
                {{ $design->product->bottle_size_ml }}ml @endif</p>
            </div>
            <div class="ds-top-actions"><a class="btn btn-secondary"
                    href="{{ route('admin.designs.show', $design) }}">Preview</a><button class="btn btn-secondary"
                    type="button" id="newVersionBtn">＋ New Version</button><button class="btn btn-primary" type="button"
                    id="saveDesignBtn">Save Draft</button></div>
        </div>

        <div class="ds-workspace">
            <aside class="ds-editor-panel">
                <div class="ds-step-tabs"><button class="active" data-tab="brand">Brand</button><button
                        data-tab="content">Content</button><button data-tab="style">Style</button><button data-tab="qr">QR &
                        Print</button></div>
                <div class="ds-panel-scroll">
                    <div class="ds-editor-section active" data-panel="brand">
                        <div class="ds-panel-title">Brand assets <small>Customer identity</small></div><label>Logo <div
                                class="ds-upload"><input type="file" id="logoUpload"
                                    accept="image/png,image/jpeg,image/webp,image/svg+xml"><span>＋</span>
                                <div><b>Upload logo</b><small>PNG, JPG, WEBP or SVG · max 4MB</small></div>
                            </div></label>
                        <div class="ds-mini-grid"><label>Brand type<select
                                    id="designType">@foreach(['restaurant', 'hotel', 'cafe', 'event', 'corporate', 'premium'] as $t)
                                        <option value="{{ $t }}" @selected($design->design_type === $t)>{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select></label><label>Font<select id="fontFamily">
                                    <option>Inter</option>
                                    <option>Georgia</option>
                                    <option>Arial</option>
                                    <option>Montserrat</option>
                                </select></label></div>
                    </div>
                    <div class="ds-editor-section" data-panel="content">
                        <div class="ds-panel-title">Label content <small>Front & back are independent</small></div>
                        <div class="ds-side-switch"><button class="active" data-side="front">Front</button><button
                                data-side="back">Back</button></div>
                        <div id="contentFields"></div>
                    </div>
                    <div class="ds-editor-section" data-panel="style">
                        <div class="ds-panel-title">Visual style <small>Live on the bottle</small></div>
                        <div class="ds-color-row"><label>Background<input type="color"
                                    id="bgColor"></label><label>Accent<input type="color" id="accentColor"></label></div>
                        <label>Logo scale<input type="range" id="logoScale" min="0.6" max="1.8"
                                step="0.05"></label><label>Logo vertical position<input type="range" id="logoY" min="5"
                                max="35" step="1"></label><label>QR size<input type="range" id="qrSize" min="12" max="34"
                                step="1"></label>
                        <div class="ds-style-presets"><button data-bg="#ffffff"
                                data-accent="#111827">Minimal</button><button data-bg="#f7f1e7"
                                data-accent="#7a2e2e">Restaurant</button><button data-bg="#0f172a"
                                data-accent="#ffffff">Luxury</button><button data-bg="#eefbff"
                                data-accent="#0794c9">Fresh</button></div>
                    </div>
                    <div class="ds-editor-section" data-panel="qr">
                        <div class="ds-panel-title">QR & print handoff <small>Destination stored with this version</small>
                        </div><label>QR destination URL<input type="url" id="qrUrl"
                                placeholder="https://example.com/menu"></label>
                        <div class="ds-qr-help"><b>Recommended</b><span>Use a permanent menu/landing URL so printed bottles
                                keep working even if your menu changes.</span></div><label>Version name<input
                                id="versionName" value="{{ $version?->name }}"></label><label>Change note<textarea
                                id="changeNote" rows="3"
                                placeholder="What changed in this version?">{{ $version?->change_note }}</textarea></label>
                    </div>
                </div>
            </aside>

            <section class="ds-canvas-area">
                <div class="ds-canvas-toolbar">
                    <div class="ds-view-switch"><button class="active" data-view="front">Front Label</button><button
                            data-view="back">Back Label</button></div><span>Live preview · click side to edit</span><button
                        id="zoomReset" type="button">Reset view</button>
                </div>
                <div class="ds-stage">
                    <div class="ds-bottle-shadow"></div>
                    <div class="ds-bottle">
                        <div class="ds-cap"></div>
                        <div class="ds-neck"></div>
                        <div class="ds-body">
                            <div class="ds-label" id="previewLabel">
                                <div class="ds-logo-slot" id="previewLogo">
                                    {{ $version?->logo_path ? "" : "J" }}@if($version?->logo_path)<img
                                    src="{{ asset("storage/" . $version->logo_path) }}" alt="Logo">@endif</div>
                                <div class="ds-label-title" id="previewTitle"></div>
                                <div class="ds-label-subtitle" id="previewSubtitle"></div>
                                <div class="ds-label-body" id="previewBody"></div>
                                <div class="ds-qr" id="previewQr">
                                    <div class="ds-qr-pattern"></div>
                                </div>
                                <div class="ds-label-footer" id="previewFooter"></div>
                            </div>
                        </div>
                        <div class="ds-base"></div>
                    </div>
                    <div class="ds-orbit orbit-a"></div>
                    <div class="ds-orbit orbit-b"></div>
                </div>
                <div class="ds-stage-meta"><span><b>Front</b> · 1 printable label</span><span><b>Back</b> · 1 printable
                        label</span><span>Artwork data saved to ERP</span></div>
            </section>

            <aside class="ds-inspector">
                <div class="ds-inspector-head">
                    <div class="ds-kicker">PROJECT STATUS</div><span
                        class="ds-status ds-{{ $design->status }}"><b></b>{{ ucwords(str_replace('_', ' ', $design->status)) }}</span>
                </div>
                <div class="ds-inspector-card">
                    <span>Customer</span><strong>{{ $design->customer->business_name }}</strong><small>{{ $design->customer->customer_code }}</small>
                </div>
                <div class="ds-inspector-card">
                    <span>Product</span><strong>{{ $design->product?->name ?? 'Any bottle' }}</strong><small>{{ $design->product?->bottle_size_ml ? $design->product->bottle_size_ml . ' ml' : 'Product can be selected later' }}</small>
                </div>
                <div class="ds-inspector-card"><span>Current
                        version</span><strong>V{{ $version?->version_no ?? 1 }}</strong><small>{{ $version?->status ?? 'draft' }}</small>
                </div>
                <div class="ds-approval-box">
                    <div class="ds-kicker">WORKFLOW</div>
                    <div class="ds-flow"><span class="done">Draft</span><i>→</i><span
                            class="{{ in_array($design->status, ['in_review', 'changes_requested', 'approved']) ? 'done' : '' }}">Review</span><i>→</i><span
                            class="{{ $design->status === 'approved' ? 'done' : '' }}">Approved</span><i>→</i><span>Production</span>
                    </div>
                    <form method="POST" action="{{ route('admin.designs.submit', [$design, $version]) }}">@csrf<button
                            class="btn btn-primary ds-full" {{ in_array($version?->status, ['approved', 'submitted']) ? 'disabled' : '' }}>Submit for Review
                            →</button></form>
                </div>
                <div class="ds-hint"><b>Print-ready workflow</b>
                    <p>Keep the approved version locked. If the customer asks for a change, create a new version instead of
                        overwriting approved artwork.</p>
                </div>
            </aside>
        </div>
    </div>
    <form id="designSaveForm" method="POST" action="{{ route('admin.designs.save', [$design, $version]) }}" hidden>@csrf
        @method('PUT')<input name="design_data" id="designDataInput"><input name="version_name" id="versionNameInput"><input
            name="change_note" id="changeNoteInput"></form>
    <form id="newVersionForm" method="POST" action="{{ route('admin.designs.new-version', [$design, $version]) }}" hidden>
        @csrf<input name="design_data" id="newVersionDataInput"><input name="version_name" value="New Version"><input
            name="change_note" id="newVersionNoteInput"></form>
@endsection

<script>window.JALVAN_DESIGN = @json(['design' => $design->only(['id', 'design_code', 'status', 'design_type']), 'version' => $version?->only(['id', 'version_no', 'name', 'design_data', 'logo_path', 'status', 'change_note'])]);</script>


<script src="{{ asset('js/design-studio.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/design-studio.css') }}">