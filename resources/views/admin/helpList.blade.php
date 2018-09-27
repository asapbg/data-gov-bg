@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'help'])
    <h3>{{ uctrans('custom.help_sections') .' / '. uctrans('custom.sections') }}</h3>
    <div class="row m-b-sm">
        <div class="col-xs-12 text-right">
            <span class="badge badge-pill long-badge">
                <a href="{{ url('/admin/helpSection/add') }}">{{ __('custom.add') }}</a>
            </span>
        </div>
    </div>
    <div class="row">
        <form method="POST" class="form-horizontal">
            @include('partials.pagination')
            {{ csrf_field() }}
            <div class="col-lg-12">
                @if (!empty($helpSections))
                    <div class="table-responsive opn-tbl text-center">
                        <table class="table">
                            <thead>
                                <th>{{ utrans('custom.section') }}</th>
                                <th>{{ utrans('custom.active') }}</th>
                                <th>{{ utrans('custom.ordering') }}</th>
                                <th>{{ __('custom.action') }}</th>
                            </thead>
                            <tbody>
                                @foreach ($helpSections as $record)
                                    <tr>
                                        <td>{{ $record->name }}</td>
                                        <td>{{ $record->active ? __('custom.yes') : __('custom.no') }}</td>
                                        <td>{{ App\Category::getOrdering()[$record->ordering] }}</td>
                                        <td class="buttons">
                                            <a
                                                class="link-action"
                                                href="{{ url('admin/helpSection/edit/'. $record->id) }}"
                                            >{{ utrans('custom.edit') }}</a>
                                            <a
                                                class="link-action"
                                                href="{{ url('admin/helpSection/view/'. $record->id) }}"
                                            >{{ utrans('custom.preview') }}</a>
                                            <a
                                                class="link-action red"
                                                href="{{ url('/admin/helpSection/delete/'. $record->id) }}"
                                                data-confirm="{{ __('custom.remove_data') }}"
                                            >{{ __('custom.delete') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
            @include('partials.pagination')
        </form>
    </div>
</div>
@endsection
