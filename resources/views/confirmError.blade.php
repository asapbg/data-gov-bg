@extends('layouts.app')

@section('content')
    <div class="confirm-error container text-center">
        <form method="POST">
            {{ csrf_field() }}
            <div class="m-b-lg">
                <img class="responsive logo-error" src="{{ asset('img/opendata-logo-color.svg') }}">
            </div>
            <div class="wrap input-border-r-12">
                <span>{{ __('custom.email_confirm_error') }}</span><br>
                <button
                    type="submit"
                    name="generate"
                    class="btn btn-primary"
                >{{ __('custom.generate_new_link') }}</button>
            </div>
        </form>
    </div>
@endsection
