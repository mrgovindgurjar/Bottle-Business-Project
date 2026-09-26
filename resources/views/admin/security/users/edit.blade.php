@extends('layouts.admin')
@section('title','Edit User · JALVAN ERP')
@push('styles')<link rel="stylesheet" href="{{ asset('css/security.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/security.js') }}"></script>@endpush
@section('content')
<div class="security-page"><div class="security-head"><div><span class="security-eyebrow">SECURITY & ACCOUNTS</span><h1>Edit user</h1><p>Update account status, contact details and role.</p></div><a class="security-btn ghost" href="{{ route('admin.security.users.index') }}">← Back</a></div><form method="POST" action="{{ route('admin.security.users.update',$user) }}">@csrf @method('PUT') @include('admin.security.users._form')<div class="security-actions"><a class="security-btn ghost" href="{{ route('admin.security.users.index') }}">Cancel</a><button class="security-btn primary">Save Changes</button></div></form>
@if(auth()->user()->hasPermission('users.reset-password'))<div class="security-card security-section reset-box"><div class="section-title"><strong>Reset password</strong><span>Admin action</span></div><form method="POST" action="{{ route('admin.security.users.reset-password',$user) }}" class="field-grid"><div><label>New password<input type="password" name="password" required autocomplete="new-password"></label></div><div><label>Confirm password<input type="password" name="password_confirmation" required></label></div><div><button class="security-btn">Reset Password</button></div></form></div>@endif
</div>
@endsection
