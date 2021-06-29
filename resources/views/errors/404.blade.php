@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-xs-12 m-l-sm mt-10">
            <div class="alert alert-warning">
                {{ __('custom.page_404_text') }}
                <a href="{{ url('/') }}" class="btn btn-primary">{{ __('custom.here') }}</a>
            </div>
        </div>
    </div>
@endsection
