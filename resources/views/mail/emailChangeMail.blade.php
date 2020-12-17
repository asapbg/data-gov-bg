@extends('layouts.mail')

@section('title')

<b>{{__('custom.email_change')}}</b>

@endsection

@section('content')

{{ __('custom.greetings') }}, {{ $username }},
<br/>
<br>{{ __('custom.have_changed') }}.
{{ __('custom.to_confirm') }}:
<a href="{{ route('mailConfirmation', ['hash' => $hash, 'mail' => $mail, 'id' => $id]) }}"> {{ __('custom.confirm') }}.</a>

@endsection
