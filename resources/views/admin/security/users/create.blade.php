@extends('layouts.admin')
@section('title','Create User · JALVAN ERP')
@push('styles')<link rel="stylesheet" href="{{ asset('css/security.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/security.js') }}"></script>@endpush
@section('content')
<div class="security-page"><div class="security-head"><div><span class="security-eyebrow">SECURITY & ACCOUNTS</span><h1>Create user</h1><p>Create a secure login and assign a role.</p></div><a class="security-btn ghost" href="{{ route('admin.security.users.index') }}">← Back</a></div><form method="POST" action="{{ route('admin.security.users.store') }}">@csrf @include('admin.security.users._form')<div class="security-actions"><a class="security-btn ghost" href="{{ route('admin.security.users.index') }}">Cancel</a><button class="security-btn primary">Create User</button></div></form></div>
@endsection
