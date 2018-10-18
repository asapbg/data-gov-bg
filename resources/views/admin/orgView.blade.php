@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'organisation'])
    @include('partials.org-nav-bar', ['view' => 'view', 'organisation' => $organisation])
    @if (!empty($organisation))
        <div class="row m-t-xs">
            <div class="col-xs-12 info-box p-l-lg">
                <div class="row">
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a href="" class="followers">
                            <p>{{ $organisation->followers_count }}</p>
                            <hr>
                            <p>{{ __('custom.followers') }} </p>
                            <img src="{{ asset('/img/followers.svg') }}">
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-5 col-xs-12">
                        <a href="" class="data-sets">
                            <p>{{ $organisation->datasets_count }}</p>
                            <hr>
                            <p>{{ __('custom.data_sets') }}</p>
                            <img src="{{ asset('/img/data-sets.svg') }}">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 m-t-md">
                <div class="row">
                    <div class="col-xs-12 page-content p-sm">
                        <div class="col-xs-12 list-orgs">
                            <div class="row">
                                <div class="col-xs-12 p-md">
                                    <div class="col-xs-12 org-logo">
                                        <img class="img-responsive" src="{{ $organisation->logo }}"/>
                                    </div>
                                    <div class="col-xs-12 m-b-lg">
                                        <h3>{{ $organisation->name }}</h3>
                                        @if (!empty($organisation->description))
                                            <p><b>{{ utrans('custom.description') }}:</b></p>
                                            <p>{!! nl2br(e($organisation->description)) !!}</p>
                                        @endif
                                        @if (!empty($organisation->activity_info))
                                            <p><b>{{ utrans('custom.activity') }}:</b></p>
                                            <p>{!! nl2br(e($organisation->activity_info)) !!}</p>
                                        @endif
                                        @if (!empty($organisation->contacts))
                                            <p><b>{{ utrans('custom.contacts') }}:</b></p>
                                            <p>{!! nl2br(e($organisation->contacts)) !!}</p>
                                        @endif
                                        @if (
                                            isset($organisation->custom_fields[0])
                                            && !empty($organisation->custom_fields[0]->key)
                                        )
                                            <p><b>{{ __('custom.additional_fields') }}:</b></p>
                                            @foreach ($organisation->custom_fields as $field)
                                                <div class="row">
                                                    <div class="col-xs-6">{{ $field->key }}</div>
                                                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="text-center m-b-lg terms-hr">
                                            <hr>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                                            <div class="col-sm-6 col-xs-12">{{ $organisation->created_by }}</div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                                            <div class="col-sm-6 col-xs-12">
                                                <div>{{ $organisation->created_at }}</div>
                                            </div>
                                        </div>
                                        @if (!empty($organisation->updated_by))
                                            <div class="form-group row">
                                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                                <div class="col-sm-6 col-xs-12">
                                                    <div>{{ $organisation->updated_by }}</div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (!empty($organisation->updated_at))
                                            <div class="form-group row">
                                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                                <div class="col-sm-6 col-xs-12">
                                                    <div>{{ $organisation->updated_at }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if (\App\Role::isAdmin())
                                        <div class="col-xs-12 view-btns m-t-lg">
                                            <div class="row">
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/admin/organisations/edit/'. $organisation->uri) }}"
                                                >
                                                    {{ csrf_field() }}
                                                    <button class="btn btn-primary" type="submit" name="edit">{{ uctrans('custom.edit') }}</button>
                                                    <input type="hidden" name="view" value="1">
                                                </form>
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                >
                                                    {{ csrf_field() }}
                                                    <button
                                                        name="back"
                                                        class="btn btn-primary"
                                                    >{{ uctrans('custom.close') }}</button>
                                                </form>
                                                <form
                                                    method="POST"
                                                    class="inline-block"
                                                    action="{{ url('/admin/organisations/delete/'. $organisation->id) }}"
                                                >
                                                    {{ csrf_field() }}
                                                        <button
                                                            class="btn del-btn btn-primary"
                                                            type="submit"
                                                            name="delete"
                                                            class="del-btn"
                                                            data-confirm="{{ __('custom.delete_organisation_confirm') }}"
                                                        >{{ uctrans('custom.remove') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
