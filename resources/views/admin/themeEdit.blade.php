@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'topicsSubtopics'])

        <div class="row">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.main_theme_edit') }}</h2>
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
                                @endif
                            @endforeach
                            <div class="form-group row m-t-md">
                                <label for="file" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.file') }}:</label>
                                <div class="col-sm-6 m-b-sm">
                                    <input
                                        type="text"
                                        name="file"
                                        class="input-border-r-12 form-control doc-upload-input js-doc-input"
                                        value="{{ !empty($model->icon_file_name) ? $model->icon_file_name : '' }}"
                                        disabled
                                    >
                                    @if (isset($errors) && $errors->has('file'))
                                        <span class="error">{{ $errors->first('file') }}</span><br>
                                    @endif
                                </div>
                                <div class="col-sm-3 text-right">
                                    <button type="submit" class="btn btn-custom js-doc-btn edit">{{ __('custom.select_file') }}</button>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="active" class="col-lg-3 col-sm-3 col-xs-4 col-form-label">{{ uctrans('custom.active') }}:</label>
                                <div class="col-lg-2 col-sm-9 col-xs-8">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="active"
                                            value="1"
                                            {{ !empty($model->active) ? 'checked' : '' }}
                                        >
                                        <span class="error">{{ $errors->first('active') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="ordering" class="col-lg-3 col-form-label">{{ uctrans('custom.ordering') }}:</label>
                                <div class="col-lg-2">
                                    <input
                                        id="ordering"
                                        name="ordering"
                                        type="number"
                                        min="1"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($model->ordering) ? $model->ordering : '' }}"
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
                                    <div>{{ !empty($model->created_by) ? $model->created_by : '' }}</div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                                <div class="col-sm-6 col-xs-12">
                                    <div>{{ !empty($model->created_at) ? $model->created_at : '' }}</div>
                                </div>
                            </div>
                            @if (!empty($model->updated_by))
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->updated_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->updated_at }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <a
                                        href="{{ url('admin/themes/list/') }}"
                                        class="btn btn-primary"
                                    >
                                        {{ uctrans('custom.close') }}
                                    </a>
                                    <button type="submit" name="edit" value="1" class="m-l-md btn btn-custom">{{ uctrans('custom.edit') }}</button>
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
