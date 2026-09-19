@extends('layouts.admin')
@section('title','Edit Issue')
@section('page_title','Edit Issue')
@section('content')
<link rel="stylesheet" href="{{ asset('css/issues.css') }}">
<div class="issue-page"><div class="issue-head"><div><h1>Edit {{ $issue->issue_number }}</h1><p>Update issue details, links, assignment and resolution.</p></div></div><form method="POST" action="{{ route('admin.issues.update',$issue) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.issues._form')<div class="issue-form-actions"><a class="issue-btn" href="{{ route('admin.issues.show',$issue) }}">Cancel</a><button class="issue-btn primary">Save Changes</button></div></form></div>
@endsection
