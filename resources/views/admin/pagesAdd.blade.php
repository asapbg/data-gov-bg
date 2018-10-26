@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'pages'])

        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.page_add') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" enctype="multipart/form-data" class="form-horisontal">
                            {{ csrf_field() }}

                            @foreach($fields as $field)
                                @if($field['view'] == 'translation')
                                    @include(
                                        'components.form_groups.translation_input',
                                        ['field' => $field]
                                    )
                                @elseif($field['view'] == 'translation_txt')
                                    @include(
                                        'components.form_groups.translation_textarea',
                                        ['field' => $field]
                                    )
                                @endif
                            @endforeach
                            <div class="resource-query">
                                <div class="form-group row text-center">{{ __('custom.generate_script') }}</div>
                                <div class="form-group row">
                                    <label for="resource_uri" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.resource_uri') }}:</label>
                                    <div class="col-sm-9 col-xs-12">
                                        <input
                                            name="resource_uri"
                                            class="input-border-r-12 form-control js-resource-uri"
                                            value="{{ old('resource_uri') }}"
                                        >
                                        <span class="error js-page-err"></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="resource_version" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.resource_version') }}:</label>
                                    <div class="col-sm-9 col-xs-12">
                                        <input
                                            type="number"
                                            min="1"
                                            name="version"
                                            class="input-border-r-12 form-control js-resource-version"
                                            value="{{ old('version') }}"
                                        >
                                        <span class="error js-page-err"></span>
                                    </div>
                                </div>
                                <div class="form-group row m-b-sm">
                                    <label for="format" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.response_format') }}:</label>
                                    <div class="col-sm-9 col-xs-12">
                                        <select
                                            id="format"
                                            name="response_format"
                                            class="js-select form-control js-resource-format"
                                            data-placeholder="{{ __('custom.response_format') }}"
                                        >
                                            <option></option>
                                            @foreach ($resourceFormats as $id => $name)
                                                <option
                                                    value="{{ $id }}"
                                                    @if (!empty(old('response_format')))
                                                        {{ $id == old('response_format') ? 'selected' : '' }}
                                                    @else
                                                        {{ $id == \App\Page::RESOURCE_RESPONSE_JSON ? 'selected' : '' }}
                                                    @endif
                                                >{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-xs-12">
                                        <span
                                            class="btn btn-primary js-generate-resource-query"
                                            data-function="generate"
                                        >{{ __('custom.generate_script_btn') }}</span>
                                    </div>
                                </div>
                                <div class="form-group row m-t-md hidden js-query-script">
                                    <div class="col-xs-12">
                                        <textarea class="input-border-r-12 form-control" readonly></textarea>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-xs-12 m-t-md">
                                            <span
                                                class="btn btn-primary js-test-resource-query"
                                                data-function="test"
                                            >{{ __('custom.test_script') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row m-t-md hidden js-loader text-center">
                                    <div class="dots-loader">
                                        <div></div><div></div><div></div><div></div>
                                    </div>
                                </div>
                                <div class="form-group row m-t-md hidden js-query-result">
                                    <div class="col-xs-12">
                                        <textarea class="input-border-r-12 form-control result" readonly></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row required">
                                <label for="section" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.section') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="section"
                                        name="section_id"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select_section') }}"
                                    >
                                        <option></option>
                                        @foreach ($sections as $id => $name)
                                            <option
                                                value="{{ $id }}"
                                                {{ $id == old('section_id') ? 'selected' : '' }}
                                            >{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('section_id') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-sm m-t-md">
                                <label for="forum_link" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.forum_link') }}:</label>
                                <div class="col-sm-9">
                                    <input
                                        name="forum_link"
                                        class="input-border-r-12 form-control"
                                        value="{{ old('forum_link') }}"
                                    >
                                    @if (isset($errors) && $errors->has('forum_link'))
                                        <span class="error">{{ $errors->first('forum_link') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="help_page" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.help_page') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="help_page"
                                        name="help_page"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select') }}"
                                    >
                                        <option></option>
                                        @foreach ($helpPages as $page)
                                            <option
                                                value="{{ $page->name }}"
                                                {{ $page->name == old('help_page') ? 'selected' : '' }}
                                            >{{ $page->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('help_page') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="valid" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.valid') }}:</label>
                                <div class="col-sm-9 m-b-sm">
                                    <div class=" row">
                                        <div class="col-sm-6 m-b-sm">
                                            <div class="row">
                                                <div class="col-xs-3">{{ __('custom.from') .': ' }}</div>
                                                <div class="col-xs-9 text-left search-field admin">
                                                    <input class="datepicker input-border-r-12 form-control" name="valid_from" value="{{ old('valid_from') }}">
                                                </div>
                                                @if (isset($errors) && $errors->has('valid_from'))
                                                    <span class="error">{{ $errors->first('valid_from') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-sm-6 m-b-sm">
                                            <div class="row">
                                                <div class="col-xs-3">{{ __('custom.to') .': ' }}</div>
                                                <div class="col-xs-9 text-left search-field admin">
                                                    <input class="datepicker input-border-r-12 form-control" name="valid_to" value="{{ old('valid_to') }}">
                                                </div>
                                                @if (isset($errors) && $errors->has('valid_to'))
                                                    <span class="error">{{ $errors->first('valid_to') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-lg-3 col-sm-3 col-xs-4 col-form-label">{{ uctrans('custom.activef') }}:</label>
                                <div class="col-lg-2 col-sm-9 col-xs-8">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="active"
                                            value="1"
                                            {{ !empty(old('active')) ? 'checked' : '' }}
                                        >
                                        @if (isset($errors) && $errors->has('active'))
                                            <span class="error">{{ $errors->first('active') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button
                                        name="back"
                                        class="btn btn-primary"
                                    >{{ uctrans('custom.close') }}</button>
                                    <button type="submit" name="create" value="1" class="m-l-md btn btn-custom">{{ __('custom.add') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
