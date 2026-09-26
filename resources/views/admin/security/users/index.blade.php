@extends('layouts.admin')
@section('title','Users · JALVAN ERP')
@push('styles')<link rel="stylesheet" href="{{ asset('css/security.css') }}">@endpush
@push('scripts')<script src="{{ asset('js/security.js') }}"></script>@endpush
@section('content')
<div class="security-page">
    <div class="security-head">
        <div><span class="security-eyebrow">SECURITY & ACCOUNTS</span><h1>Users</h1><p>Manage staff and customer login accounts.</p></div>
        @if(auth()->user()->hasPermission('users.create'))<a class="security-btn primary" href="{{ route('admin.security.users.create') }}">+ New User</a>@endif
    </div>
    <form class="security-filter" method="GET">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, email, mobile or role...">
        <select name="status"><option value="">All status</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option></select>
        <button class="security-btn">Search</button>
        @if(request()->hasAny(['q','status']))<a class="security-btn ghost" href="{{ route('admin.security.users.index') }}">Clear</a>@endif
    </form>
    <div class="security-card table-wrap">
        <table class="security-table"><thead><tr><th>User</th><th>Role</th><th>Account</th><th>Last Login</th><th class="text-end">Actions</th></tr></thead><tbody>
        @forelse($users as $user)
            <tr>
                <td><div class="user-cell"><div class="security-avatar">{{ strtoupper(substr($user->name,0,1)) }}</div><div><strong>{{ $user->name }}</strong><small>{{ $user->email }} · {{ $user->mobile }}</small></div></div></td>
                <td><div class="role-pills">@foreach($user->roles as $role)<span>{{ $role->name }}</span>@endforeach</div>@if($user->customer)<small class="muted">{{ $user->customer->business_name }}</small>@endif</td>
                <td><span class="status-pill {{ $user->is_active ? 'active' : 'inactive' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td>{{ $user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</td>
                <td class="actions text-end">
                    @if(auth()->user()->hasPermission('users.edit'))<a href="{{ route('admin.security.users.edit',$user) }}">Edit</a>@endif
                    @if(auth()->user()->hasPermission('users.delete') && !$user->is(auth()->user()))
                    <form method="POST" action="{{ route('admin.security.users.destroy',$user) }}" onsubmit="return confirm('Deactivate this account?')">@csrf @method('DELETE')<button>Deactivate</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="security-empty"><strong>No users found</strong><span>Create your first staff/customer account.</span></div></td></tr>
        @endforelse
        </tbody></table>
    </div>
    <div class="security-pagination">{{ $users->links() }}</div>
</div>
@endsection
