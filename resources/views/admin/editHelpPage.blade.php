@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'help'])
    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-lg-2"></div>
            <div class="col-lg-8 frame section-edit">
                <h3 class="text-center m-b-lg">{{ uctrans('custom.edit_help_page') }}</h3>
                <div class="form-group row m-t-md required">
                    <label for="name" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.unique_identificator') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <input
                            class="input-border-r-12 form-control"
                            name="name"
                            id="name"
                            value="{{ !empty(old('name')) ? old('name') : $page->name }}"
                        >
                        <span class="error">{{ $errors->first('name') }}</span>
                    </div>
                </div>
                <div class="m-t-lg">
                    @foreach($fields as $field)
                        @if($field['view'] == 'translation')
                            @include(
                                'components.form_groups.translation_input',
                                ['field' => $field, 'model' => $page]
                            )
                        @elseif($field['view'] == 'translation_txt')
                            @include(
                                'components.form_groups.translation_textarea',
                                ['field' => $field, 'model' => $page]
                            )
                        @endif
                    @endforeach
                </div>
                <div class="form-group row m-t-md">
                    <label for="keywords" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.keywords') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <input
                            class="input-border-r-12 form-control"
                            name="keywords"
                            id="keywords"
                            value="{{ !empty(old('keywords')) ? old('keywords') : $page->keywords }}"
                        >
                        <span class="error">{{ $errors->first('keywords') }}</span>
                    </div>
                </div>
                <div class="form-group row m-b-md m-t-md">
                    <label for="section_id" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.parent_section') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                            <select
                                class="js-select"
                                name="section_id"
                                id="section_id"
                            >
                                <option value="">{{ __('custom.select') }}</option>
                                @foreach ($sections as $parent)
                                    <option
                                        value="{{ $parent->id }}"
                                        {{
                                            (!empty(old('parent')) && old('parent') == $parent->id)
                                            || $page->section_id == $parent->id
                                                ? 'selected'
                                                : ''
                                        }}
                                    >{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            <span class="error">{{ $errors->first('parent') }}</span>
                    </div>
                </div>
                <div class="form-group row m-b-md m-t-md">
                    <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.activef') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="active"
                                id="active"
                                value="1"
                                {{ !empty(old('active')) || $page->active ? 'checked' : '' }}
                            >
                            <span class="error">{{ $errors->first('active') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group row m-b-md m-t-md required">
                    <label for="order" class="col-lg-3 col-form-label">{{ uctrans('custom.ordering') }}:</label>
                    <div class="col-lg-2">
                        <input
                            id="order"
                            name="ordering"
                            type="number"
                            min="1"
                            class="input-border-r-12 form-control"
                            value="{{ $page->ordering }}"
                        >
                        <span class="error">{{ $errors->first('ordering') }}</span>
                    </div>
                </div>
                <div class="text-center m-b-lg terms-hr">
                    <hr>
                </div>
                <div class="form-group row m-b-lg m-t-md">
                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                    <div class="col-sm-6 col-xs-12">
                        <div>{{ $page->created_by }}</div>
                    </div>
                </div>
                <div class="form-group row m-b-lg m-t-md">
                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                    <div class="col-sm-6 col-xs-12">
                        <div>{{ $page->created_at }}</div>
                    </div>
                </div>
                @if (!empty($page->updated_by))
                    <div class="form-group row m-b-lg m-t-md">
                        <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                        <div class="col-sm-6 col-xs-12">
                            <div>{{ $page->updated_by }}</div>
                        </div>
                    </div>
                    <div class="form-group row m-b-lg m-t-md">
                        <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                        <div class="col-sm-6 col-xs-12">
                            <div>{{ $page->updated_at }}</div>
                        </div>
                    </div>
                @endif
                <div class="form-group row">
                    <div class="col-sm-12 text-right">
                        <button
                            type="submit"
                            name="back"
                            class="btn btn-primary"
                        >{{ uctrans('custom.close') }}</button>
                        <button
                            type="submit"
                            name="save"
                            class="m-l-md btn btn-custom"
                        >{{ __('custom.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
