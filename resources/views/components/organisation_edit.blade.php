<div class="col-xs-12 m-t-lg">
    <div>
        <h2>{{ __('custom.edit_org') }}</h2>
        <p class='req-fields m-t-lg m-b-lg'>{{ __('custom.all_fields_required') }}</p>
    </div>
    <form method="POST" class="m-t-lg" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="form-group row">
            <label class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.image') }}:</label>
            <div class="col-sm-9">
                <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md">
                    <img
                            class="preview js-preview {{ empty($model['logo']) ? 'hidden' : '' }}"
                            src="{{ !empty($model['logo']) ? $model['logo'] : '' }}"
                            alt="organisation logo"
                    />
                </div>
                <div class="inline-block choose-img">
                    <span class="badge badge-pill"><label class="js-logo" for="logo">{{ uctrans('custom.select_image') }}</label></span>
                    <input class="hidden js-logo-input" type="file" name="logo" value="">
                </div>
                <div class="error">{{ $errors->first('logo') }}</div>
            </div>
        </div>
        @if(\Auth::user()->is_admin)
        <div class="form-group row">
            <label class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.precept') }}:</label>
            <div class="col-sm-9">
                <div class="inline-block choose-precept">
                    <span class="badge badge-pill"><label class="js-precept" for="precept">{{ uctrans('custom.select_precept') }}</label></span>
                    <input class="hidden js-precept-input" type="file" name="precept" value="">
                    @if($model['precept'])
                        <a href="{{ $model['precept']['path'] }}" target="_blank" id="precept_file">
                            <i class="fa fa-file-pdf-o red"></i> {{ $model['precept']['name'] }}
                        </a>
                    @endif
                    <span id="priview-precept">
                        <i class="precept-type"></i>
                        <span class="file-name"></span>
                    </span>
                </div>
                <div class="error">{{ $errors->first('precept') }}</div>
            </div>
        </div>
        @endif
        <div class="form-group row {{ !empty($errors->parent_org_id) ? 'has-error' : '' }}">
            <label for="baseOrg" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.main_organisation') }}:</label>
            <div class="col-sm-9">
                <select
                        class="input-border-r-12 form-control js-autocomplete"
                        name="parent_org_id"
                        id="filter"
                        @if(!\Auth::user()->is_admin) disabled="disabled" @endif
                        data-live-search="true"
                >
                    @if (isset($parentOrgs[0]))
                        <option value="">&nbsp;</option>
                        @foreach ($parentOrgs as $parent)
                            @if (!isset($model['name']) || $model['id'] != $parent->id)
                                <option
                                        value="{{ $parent->id }}"
                                        {{ !empty($model['parent_org_id']) && $parent->id == $model['parent_org_id']
                                            ? 'selected'
                                            : ''
                                        }}
                                >{{ $parent->name }}</option>
                            @endif
                        @endforeach
                    @else
                        <option value="" selected >{{ __('custom.no_info') }}</option>
                    @endif
                </select>
                @if (isset($errors) && $errors->has('parent_org_id'))
                    <span class="error">{{ $errors->first('parent_org_id') }}</span>
                @endif
            </div>
        </div>
        <div class="form-group row {{ !empty($errors->uri) ? 'has-error' : '' }}">
            <label for="uri" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.unique_identificator') }}:</label>
            <div class="col-sm-9">
                <input
                        type="text"
                        class="input-border-r-12 form-control"
                        name="uri"
                        @if(!\Auth::user()->is_admin) readonly="readonly" @endif
                        value="{{ !empty($model['uri']) ? $model['uri'] : '' }}"
                >
                @if (isset($errors) && $errors->has('uri'))
                    <span class="error">{{ $errors->first('uri') }}</span>
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
            <label for="type" class="col-md-3 col-sm-2 col-xs-12 col-form-label">{{ __('custom.type') }}:</label>
            @foreach (\App\Organisation::getPublicTypes() as $id => $name)
                <div class="col-sm-4 col-xs-6 m-b-m p-r-none">
                    <label class="radio-label">
                        {{ utrans($name) }}
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
            @if (isset($errors) && $errors->has('type'))
                <div class="row m-l-md">
                    <div class="col-xs-12 m-l-md">
                        <span class="error">{{ $errors->first('type') }}</span>
                    </div>
                </div>
            @endif
        </div>
        <div class="form-group row">
            <div class="col-md-6 col-xs-12 p-l-none">
                <label for="active" class="col-sm-2 col-xs-4 col-form-label">{{ uctrans('custom.activef') }}:</label>
                <div class="col-lg-4 col-sm-4 col-xs-8">
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
        </div>
        @if (\Auth::user()->is_admin)
            <div class="form-group row">
                <div class="col-md-6 col-xs-12 p-l-none">
                    <label for="active" class="col-sm-2 col-xs-4 col-form-label">{{ __('custom.org_approved') }}:</label>
                    <div class="col-lg-4 col-sm-4 col-xs-8">
                        <div class="js-check">
                            <input
                                    type="checkbox"
                                    name="approved"
                                    value="1"
                                    {{ !empty($model['approved']) ? 'checked' : '' }}
                            >
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="form-group row">
            <div class="col-sm-12 text-right">
                <a
                        href="{{ url('/'. $root .'/organisations') }}"
                        class="btn btn-primary"
                >
                    {{ uctrans('custom.close') }}
                </a>
                <button type="submit" name="save" class="m-l-md btn btn-primary">{{ uctrans('custom.save') }}</button>
            </div>
        </div>
        <input type="hidden" name="org_id" value="{{ !empty($model['id']) ? $model['id'] : '' }}">
    </form>
</div>