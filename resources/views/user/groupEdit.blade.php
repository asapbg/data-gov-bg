@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    @include('components.group_edit')
</div>
@endsection
