@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-1 m-t-md p-l-r-none">
        <div class="row">
            <div class="col-xs-12">
                @if (!empty($success))
                    <div class="alert alert-success">
                        {{ $success }}
                    </div>
                @endif
                @if (isset($result->error))
                    <div class="alert alert-danger">
                        {{ $result->error->message }}
                    </div>
                @endif
                <div>
                    <h2>Редакция на организация</h2>
                    <p class='req-fields m-t-lg m-b-lg'>Всички полета маркирани с * са задължителни.</p>
                </div>
                <form method="POST" action="{{ url('/user/organisation/edit') }}" class="m-t-lg" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <label class="col-sm-3 col-xs-12 col-form-label">Изображение:</label>
                        <div class="col-sm-9">
                            <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md">
                                <img
                                    class="preview js-preview {{ empty($model['logo']) ? 'hidden' : '' }}"
                                    src="{{ !empty($model['logo']) ? $model['logo'] : '' }}"
                                    alt="organisation logo"
                                />
                            </div>
                            <div class="inline-block">
                                <span class="badge badge-pill"><label class="js-logo" for="logo">избери изображение</label></span>
                                <input class="hidden js-logo-input" type="file" name="logo" value="">
                                @if (isset($result->errors->logo))
                                    <span class="error">{{ $result->errors->logo[0] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group row {{ !empty($errors->parent_org_id) ? 'has-error' : '' }}">
                        <label for="baseOrg" class="col-sm-3 col-xs-12 col-form-label">Основна организация:</label>
                        <div class="col-sm-9">
                            <input
                                type="text"
                                class="input-border-r-12 form-control"
                                name="parent_org_id"
                                value="{{ !empty($model['parent_org_id']) ? $model['parent_org_id'] : '' }}"
                            >
                            @if (isset($result->errors->parent_org_id))
                                <span class="error">{{ $result->errors->parent_org_id[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row {{ !empty($errors->uri) ? 'has-error' : '' }}">
                        <label for="uri" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                        <div class="col-sm-9">
                            <input
                                type="text"
                                class="input-border-r-12 form-control"
                                name="uri"
                                value="{{ !empty($model['uri']) ? $model['uri'] : '' }}"
                            >
                            @if (isset($result->errors->uri))
                                <span class="error">{{ $result->errors->uri[0] }}</span>
                            @endif
                        </div>
                    </div>
                    @foreach($fields as $field)
                        @if($field['view'] == 'translation')
                            @include('components.form_groups.translation_input', ['field' => $field, 'model' => $model])
                        @elseif($field['view'] == 'translation_txt')
                            @include('components.form_groups.translation_textarea', ['field' => $field, 'model' => $model])
                        @elseif($field['view'] == 'translation_custom')
                            @include('components.form_groups.translation_custom_fields', ['field' => $field, 'model' => $withModel])
                        @endif
                    @endforeach
                    <div class="form-group row {{ !empty($errors->type) ? 'has-error' : '' }} required">
                        <label for="type" class="col-lg-3 col-sm-3 col-xs-12 col-form-label">Тип:</label>
                        @foreach (\App\Organisation::getPublicTypes() as $id => $name)
                            <div class="col-lg-4 col-md-4 col-xs-12 m-b-md">
                                <label class="radio-label">
                                    {{ $name }}
                                    <div class="js-check">
                                        <input
                                            type="radio"
                                            name="type"
                                            value="{{ $id }}"
                                            {{ isset($model['type']) && $model['type'] == $id ? 'checked' : '' }}
                                        >
                                    </div>
                                </label>
                            </div>
                        @endforeach
                        @if (isset($result->errors->type))
                            <span class="error">{{ $result->errors->type[0] }}</span>
                        @endif
                    </div>
                    <div class="form-group row">
                        <label for="active" class="col-sm-3 col-xs-12 col-form-label">Активнa:</label>
                        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                            <div class="js-check">
                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    {{ !empty($model['active']) ? 'checked' : '' }}
                                >
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12 text-right">
                            <button type="submit" class="m-l-md btn btn-primary">готово</button>
                        </div>
                    </div>
                    <input type="hidden" name="org_id" value="{{ !empty($model['id']) ? $model['id'] : '' }}">
                </form>
            </div>
        </div>
    </div>
</div>
@endsection