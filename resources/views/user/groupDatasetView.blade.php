@extends('layouts.app')
@php $root = !(Auth::user()->is_admin) ? 'user' : 'admin'; @endphp
@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.'. $root .'-nav-bar', ['view' => 'group'])
        @include('partials.group-nav-bar', ['view' => 'dataset', 'group' => $group])
        <div class="col-sm-3 col-xs-12 text-left sidenav">
            @include('partials.group-info', ['group' => $group])
        </div>
        @include('components.datasets.dataset_view', ['admin' => Auth::user()->is_admin])
    </div>
@endsection
