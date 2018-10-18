@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'languages'])
    <div class="col-xs-12 m-t-lg p-l-r-none">
        <span class="my-profile m-l-sm">{{ uctrans('custom.language_list') }}</span>
    </div>

    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-xs-12">
                <a class="btn btn-primary pull-right add" href="{{ url('/admin/languages/add') }}">{{ uctrans('custom.add') }}</a>
            </div>
            <div class="col-xs-12">
                <div class="table-responsive opn-tbl text-center">
                    <table class="table">
                        <thead>
                            <th>{{ __('custom.language') }}</th>
                            <th>{{ __('custom.code') }}</th>
                            <th>{{ __('custom.activity') }}</th>
                            <th>{{ __('custom.action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($languages as $lang)
                                <tr>
                                    <td>{{ $lang->name }}</td>
                                    <td>{{ $lang->locale }}</td>
                                    <td>{{ $lang->active ? __('custom.active') : __('custom.not_active') }}</td>
                                    <td class="buttons">
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/languages/edit/'. $lang->locale) }}"
                                        >{{ utrans('custom.edit') }}</a>
                                        <a
                                            class="link-action red"
                                            href="{{ url('/admin/languages/delete/'. $lang->locale) }}"
                                            data-confirm="{{ __('custom.remove_data') }}"
                                        >{{ __('custom.delete') }}</a>
                                    </td>
                                </tr>
                                <input type="hidden" name="id" value="{{ $lang->locale }}">
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
