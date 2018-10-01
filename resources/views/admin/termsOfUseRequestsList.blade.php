
@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'termsConditionsReq'])
        <div class="col-xs-12 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{uctrans('custom.terms_of_use_list')}}</span>
        </div>
        @include('partials.pagination')
        <div class="row m-b-lg">
            <div class="col-sm-3 hidden-xs"></div>
            <div class="col-sm-9 col-xs-12 m-t-lg m-b-md p-l-lg">{{ __('custom.order_by') }}</div>
            <div class="col-sm-3 hidden-xs"></div>
            <div class="col-sm-9 col-xs-12 p-l-lg order-documents">
                <a
                    href="{{
                        action(
                            'Admin\TermsOfUseRequestController@list',
                            array_merge(
                                ['order' => 'created_at'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.req_creation_date') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\TermsOfUseRequestController@list',
                            array_merge(
                                ['order' => 'status'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'status'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.status') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\TermsOfUseRequestController@list',
                            array_merge(
                                ['order_type' => 'asc'],
                                array_except(app('request')->input(), ['order_type'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_type'])
                        && app('request')->input()['order_type'] == 'asc'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.order_asc') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\TermsOfUseRequestController@list',
                            array_merge(
                                ['order_type' => 'desc'],
                                array_except(app('request')->input(), ['order_type'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_type'])
                        && app('request')->input()['order_type'] == 'desc'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.order_desc') }}</a>
            </div>
        </div>
        <div class="row m-b-lg">
            <div class="col-sm-3 sidenav col-xs-12 m-t-md">
                <form
                    method="GET"
                    action="{{ action('Admin\TermsOfUseRequestController@list', []) }}"
                >
                    <div class="row m-b-sm">
                        <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                        <div class="col-md-7 col-sm-8 text-left search-field admin">
                            <input class="js-from-filter datepicker input-border-r-12 form-control" name="from" value="{{ $range['from'] }}">
                        </div>
                    </div>
                    <div class="row m-b-sm">
                        <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                        <div class="col-md-7 col-sm-8 text-left search-field admin">
                            <input class="js-to-filter datepicker input-border-r-12 form-control" name="to" value="{{ $range['to'] }}">
                        </div>
                    </div>
                    @if (isset(app('request')->input()['status']))
                        <input type="hidden" name="status" value="{{ app('request')->input()['status'] }}">
                    @endif
                    @if (isset(app('request')->input()['order']))
                        <input type="hidden" name="order" value="{{ app('request')->input()['order'] }}">
                    @endif
                    @if (isset(app('request')->input()['q']))
                        <input type="hidden" name="q" value="{{ app('request')->input()['q'] }}">
                    @endif
                </form>
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.status') }}</a>
                        <ul class="sidebar-submenu m-b-md">
                            @foreach ($statuses as $key => $status)
                                <li>
                                    <a
                                        href="{{
                                            action(
                                                'Admin\TermsOfUseRequestController@list',
                                                array_merge(
                                                    ['status' => $key],
                                                    array_except(app('request')->input(), ['status', 'page'])
                                                )
                                            )
                                        }}"
                                        class="{{
                                            isset(app('request')->input()['status'])
                                            && app('request')->input()['status'] == $key
                                                ? 'active'
                                                : ''
                                        }}"
                                    >{{ uctrans('custom.'. $status) }}</a>
                                </li>
                            @endforeach
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\TermsOfUseRequestController@list',
                                            array_except(app('request')->input(), ['status', 'page'])
                                        )
                                    }}"
                                >{{ uctrans('custom.all') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="form-group">
                    <div class="col-lg-10 col-md-12 search-field admin">
                        <form
                            method="GET"
                            action="{{ action('Admin\TermsOfUseRequestController@list', []) }}"
                        >
                            <input
                                type="text"
                                class="m-t-md input-border-r-12 form-control"
                                placeholder="{{ __('custom.search') }}"
                                value="{{ isset($search) ? $search : '' }}"
                                name="q"
                            >
                            @foreach (app('request')->except(['q', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $innerValue)
                                        <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                                    @endforeach
                                @else
                                    <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                                @endif
                            @endforeach
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-9 col-xs-12 m-t-md">
                <div class="row">
                    @if (count($terms))
                        @foreach ($terms as $index => $request)
                            <div class="col-xs-12 {{ $index ? 'm-t-lg' : '' }}">
                                <div class="col-xs-6">{{ utrans('custom.request') }} - {{ $request->created_by }} - {{ $request->created_at }}</div>
                                <div
                                    class="col-xs-6 text-right js-terms-req-preview"
                                    data-index="{{ $index }}"
                                    data-action="show"
                                >
                                    <span class="badge badge-pill m-b-sm">{{ uctrans('custom.preview') }}</span>
                                </div>
                                <div class="hidden {{ 'js-terms-req-cont-'. $index }}">
                                    <div class="col-xs-12">{{ $request->firstname .' '. $request->lastname }}</div>
                                    <div class="col-xs-12 m-t-md">{{ $request->email }}</div>
                                    <div class="col-xs-12 m-t-xs">{{ $request->description }}</div>
                                    <div class="col-md-3 col-sm-4 col-xs-6 terms-hr"><hr/></div>
                                    <div class="col-xs-12">{{ __('custom.created_at') }}: &nbsp; {{ $request->created_at }}</div>
                                    <div class="col-xs-12">{{ __('custom.created_by') }}: &nbsp; {{ $request->created_by }}</div>
                                    <div class="col-xs-12">{{ __('custom.updated_at') }}: &nbsp; {{ $request->updated_at }}</div>
                                    <div class="col-xs-12">{{ __('custom.updated_by') }}: &nbsp; {{ $request->updated_by }}</div>
                                </div>
                            </div>
                            <div class="col-xs-12 text-right m-t-xs m-b-lg {{ 'js-terms-req-btns-'. $index }} hidden">
                                <span
                                    class="badge badge-pill m-r-md m-b-sm js-terms-req-close"
                                    data-index="{{ $index }}"
                                    data-action="close"
                                >{{ __('custom.close') }}</span>
                                <span class="badge badge-pill m-r-md m-b-sm">
                                    <a
                                        href="{{ url('/admin/terms-of-use-request/edit/'. $request->id) }}"
                                    >{{ uctrans('custom.edit') }}</a>
                                </span>
                                <span class="badge del-btn badge-pill m-r-md m-b-sm">
                                    <a
                                        href="{{ url('/admin/terms-of-use-request/delete/'. $request->id) }}"
                                        data-confirm="{{ __('custom.remove_data') }}"
                                    >{{ __('custom.delete') }}</a>
                                </span>
                            </div>
                        @endforeach
                    @else
                        <div class="col-sm-12 m-t-xl text-center no-info">
                            {{ __('custom.no_info') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (isset($pagination))
            <div class="row">
                <div class="col-xs-12 text-center">
                    {{ $pagination->render() }}
                </div>
            </div>
        @endif
    </div>
@endsection
