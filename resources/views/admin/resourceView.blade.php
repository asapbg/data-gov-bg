@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'user'])
    @include('components.datasets.resource_view', ['admin' => true])
</div>
@endsection
