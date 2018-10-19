@if ($resource->type == App\Resource::getTypes()[App\Resource::TYPE_HYPERLINK])
    <a href="{{ $resource->resource_url }}">{{ $resource->resource_url }}</a>
@else
    @if (empty($data))
        <div class="col-sm-12 m-t-lg text-center">{{ __('custom.no_info') }}</div>
    @else
        @if ($resource->format_code == App\Resource::FORMAT_CSV)
            <div class="m-b-lg overflow-x-auto js-show-on-load">
                <table class="data-table">
                    <thead>
                        @foreach ($data as $index => $row)
                            @if ($index == 0)
                                @foreach ($row as $key => $value)
                                    <th><p>{{ $value }}</p></th>
                                @endforeach
                                </thead>
                                <tbody>
                            @else
                                <tr>
                                    @foreach ($row as $key => $value)
                                        <td>{{ $value }}</td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($resource->format_code == App\Resource::FORMAT_XML || $resource->format_code == App\Resource::FORMAT_RDF)
            <textarea
                class="js-xml-prev col-xs-12 m-b-md"
                data-xml-data="{{ $data }}"
                rows="20"
            ></textarea>
        @elseif ($resource->format_code == App\Resource::FORMAT_JSON && isset($data->text))
            <p>@php echo nl2br(e($data->text)) @endphp</p>
        @elseif ($resource->format_code == App\Resource::FORMAT_XSD)
            @foreach ($data as $row)
                <p>{{ $row }}</p>
            @endforeach
        @else
            <p>{{ uctrans('custom.resource_no_visualization') }}</p>
        @endif
        <form method="POST" action="{{ url('/resource/download') }}">
            {{ csrf_field() }}
            <input
                hidden
                name="resource"
                type="text"
                value="{{ $resource->id }}"
            >
            <input
                hidden
                name="version"
                type="text"
                value="{{ $versionView }}"
            >
            <input
                hidden
                name="name"
                type="text"
                value="{{ $resource->name }}"
            >
            <div class="form-group row">
                <label
                    for="format"
                    class="col-sm-3 col-xs-12 col-form-label"
                >{{ uctrans('custom.format') }}:</label>
                <div class="col-sm-9">
                    <select
                        id="format"
                        name="format"
                        class="js-select form-control"
                    >
                        @foreach ($formats as $id => $format)
                            <option
                                value="{{ $format }}"
                                {{ $format == $resource->file_format ? 'selected' : '' }}
                            >{{ $format }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('category_id') }}</span>
                </div>
            </div>

            <div class="row text-right download-btns">
                <button
                    name="download"
                    type="submit"
                    class="btn btn-primary js-ga-event"
                    data-ga-action="download"
                    data-ga-label="resource download"
                    data-ga-category="data"
                >{{ uctrans('custom.download') }}</button>
            </div>
        </form>
    @endif
@endif
