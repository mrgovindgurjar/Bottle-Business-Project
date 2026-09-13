@extends('layouts.admin')
@push('styles')<link rel="stylesheet" href="{{ asset('css/staff-attendance.css') }}">@endpush
@section('content')<div class="hr-head"><div><div class="hr-kicker">HR MANAGEMENT</div><h1>Add Staff</h1><p>Create a new employee record.</p></div></div>@if($errors->any())<div class="hr-alert hr-error">{{ $errors->first() }}</div>@endif<form method="POST" action="{{ route('admin.staff.store') }}" class="hr-card">@csrf @include('admin.staff._form')</form>@endsection
