@extends('layouts.admin')
@section('title','Create Issue')
@section('page_title','Create Issue')
@section('content')
<link rel="stylesheet" href="{{ asset('css/issues.css') }}">
<div class="issue-page"><div class="issue-head"><div><h1>Create Issue</h1><p>Create a complaint, operational issue or support ticket.</p></div></div><form method="POST" action="{{ route('admin.issues.store') }}" enctype="multipart/form-data">@csrf @include('admin.issues._form')<div class="issue-form-actions"><a class="issue-btn" href="{{ route('admin.issues.index') }}">Cancel</a><button class="issue-btn primary">Create Issue</button></div></form></div>
@endsection
