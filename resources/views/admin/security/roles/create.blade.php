@extends('layouts.admin')
@section('title','Create Role · JALVAN ERP')
@push('styles')<link rel="stylesheet" href="{{ asset('css/security.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/security.js') }}"></script>@endpush
@section('content')<div class="security-page"><div class="security-head"><div><span class="security-eyebrow">SECURITY & ACCOUNTS</span><h1>Create role</h1><p>Build a reusable permission set for your team.</p></div><a class="security-btn ghost" href="{{ route('admin.security.roles.index') }}">← Back</a></div><form method="POST" action="{{ route('admin.security.roles.store') }}">@csrf @include('admin.security.roles._form')<div class="security-actions"><a class="security-btn ghost" href="{{ route('admin.security.roles.index') }}">Cancel</a><button class="security-btn primary">Create Role</button></div></form></div>@endsection
