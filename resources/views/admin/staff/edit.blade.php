@extends('layouts.admin')
@push('styles')<link rel="stylesheet" href="{{ asset('css/staff-attendance.css') }}">@endpush
@section('content')<div class="hr-head"><div><div class="hr-kicker">HR MANAGEMENT</div><h1>Edit {{ $staff->full_name }}</h1><p>Update employee information.</p></div></div>@if($errors->any())<div class="hr-alert hr-error">{{ $errors->first() }}</div>@endif<form method="POST" action="{{ route('admin.staff.update',$staff) }}" class="hr-card">@csrf @method('PUT') @include('admin.staff._form',['staff'=>$staff])</form>@endsection
