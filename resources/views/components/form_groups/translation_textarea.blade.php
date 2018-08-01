
@foreach (Lang::getInstance()->getActive() as $key => $active)
    <div
        class="
            form-group
            row
            {{ !empty($field['required']) && !$key ? 'required' : '' }}
            {{ isset($result->errors->{ $field['name'] }) ? 'has-error' : '' }}
        "
    >
        <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ !$key ? $field['label'] . ':' : '' }}</label>
        <div class="col-sm-9">
            <div class="input-group">
                @if (isset($model) && empty(old($field['name'])))
                    <textarea
                        name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                        class="input-border-r-12 form-control"
                    >{{ $model->translate($active['locale'], $active['locale'])->{ $field['name'] } }}</textarea>
                @elseif (is_array(old($field['name'])) && !empty(old($field['name'])[$active['locale']]))
                    <textarea
                        name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                        class="input-border-r-12 form-control"
                    >{{ old($field['name'])[$active['locale']] }}</textarea>
                @else
                    <textarea
                        name="{{ $field['name'] }}[{{ $active['locale'] }}]"
                        class="input-border-r-12 form-control"
                        value=""
                    ></textarea>
                @endif
                <span class="input-group-addon">
                    <span class="flag-icon flag-icon-{{ locale_to_flag($active['locale']) }}"></span>
                </span>
            </div>
            @if (isset($result->errors->{ $field['name'] }))
                <span class="error">{{ $result->errors->{ $field['name'] }[0] }}</span>
            @endif
        </div>
    </div>
@endforeach
