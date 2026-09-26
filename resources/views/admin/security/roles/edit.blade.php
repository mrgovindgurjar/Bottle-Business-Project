@extends('layouts.admin')
@section('title','Edit Role · JALVAN ERP')
@push('styles')<link rel="stylesheet" href="{{ asset('css/security.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/security.js') }}"></script>@endpush
@section('content')<div class="security-page"><div class="security-head"><div><span class="security-eyebrow">SECURITY & ACCOUNTS</span><h1>Edit role</h1><p>Update role permissions and availability.</p></div><a class="security-btn ghost" href="{{ route('admin.security.roles.index') }}">← Back</a></div><form method="POST" action="{{ route('admin.security.roles.update',$role) }}">@csrf @method('PUT') @include('admin.security.roles._form')<div class="security-actions"><a class="security-btn ghost" href="{{ route('admin.security.roles.index') }}">Cancel</a><button class="security-btn primary">Save Role</button></div></form></div>@endsection
