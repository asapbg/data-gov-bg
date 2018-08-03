@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-md-3 col-sm-4 col-xs-12 sidenav">
                    <span class="my-profile m-b-lg m-l-sm">Моят профил</span>
                    <ul class="nav">
                        <li class="js-show-submenu m-t-lg">
                            <ul class="sidebar-submenu open">
                                <li><a href="{{ url('/user/') }}">{{ utrans('custom.users', 2) }}</a></li>
                                <li><a href="{{ url('/user/organisations') }}">{{ utrans('custom.organisations', 2) }}</a></li>
                                <li><a href="{{ url('/user/userGroups') }}">{{ utrans('custom.groups', 2) }}</a></li>
                                <li><a href="{{ url('/user/datasets') }}">{{ __('custom.data_sets') }}</a></li>
                                <li><a href="{{ url('/user/') }}">{{ __('custom.main_topic') }}</a></li>
                                <li><a href="{{ url('/user/') }}">{{ __('custom.labels') }}</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-md-9 col-sm-8 col-xs-12 p-sm">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="active p-l-none" href="{{ url('/user') }}">{{ __('custom.notifications') }}</a></li>
                                            <li><a href="{{ url('/user/datasets') }}">{{ __('custom.my_data') }}</a></li>
                                            <li><a href="{{ url('/user/userGroups') }}">{{ utrans('custom.groups', 2) }}</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">{{ utrans('custom.organisations', 2) }}</a></li>
                                            <li><a href="{{ url('/user/settings') }}">{{ __('custom.settings') }}</a></li>
                                            <li><a href="{{ url('/user/invite') }}">{{ __('custom.invite') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            {{ $pagination->render() }}
                        </div>
                    </div>
                    <div class="col-xs-12 p-sm chronology">
                        @foreach ($actionsHistory as $actionHistory)
                        @php
                            $tsDiff = time() - strtotime($actionHistory->occurrence);
                            $min = floor($tsDiff / 60);
                            $hours = floor($tsDiff / 3600);
                            $days = floor($tsDiff / 86000);
                            $objName = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_name'];
                            $objType = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_type'];
                            $objView = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_view'];
                            $parentObjId = $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_id'];
                        @endphp
                        <div class="row">
                            <div class="col-xs-1 p-l-none">
                                <img class="img-thumnail" src="{{ asset('img/'. $objType .'-icon.svg') }}"/>
                            </div>
                            <div class="col-xs-11 p-h-sm">
                                <div class="col-md-1 col-xs-2 logo-img">
                                    <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                                </div>
                                <div class="col-md-10 col-xs-10">
                                    <div>{{ __('custom.date_added') }}: {{ date('d.m.Y', strtotime($actionHistory->occurrence)) }}</div>
                                    <a href="{{ url('/user/profile') }}"><h3>{{ $actionHistory->user_firstname }} {{ $actionHistory->user_lastname }}</h3></a>
                                    <p>
                                        {{ $actionTypes[$actionHistory->action] }} {{ $actionHistory->module }}
                                        @if ($objView != '')
                                            <a href="{{ url($objView) }}"><b>{{ $objName }}</b></a>
                                        @else
                                            <b>{{ $objName }}</b>
                                        @endif
                                        @if ($parentObjId != '')
                                             към {{ $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_type'] }}
                                             <a href="{{ url($actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_view']) }}">
                                                <b>{{ $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_name'] }}</b></a>
                                        @endif
                                        -
                                        @if ($hours == 24)
                                        {{ __('custom.one_day_ago') }}
                                        @elseif ($hours > 24)
                                            преди {{ $days }} дни
                                        @elseif ($min == 60)
                                        {{ __('custom.one_hour_ago') }}
                                        @elseif ($min > 60)
                                            преди {{ $hours }} часа
                                        @else
                                            {{ $min }} {{ utrans('custom.minutes', 2) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            {{ $pagination->render() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
