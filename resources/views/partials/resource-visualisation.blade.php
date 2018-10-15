@if ($resource->type == App\Resource::getTypes()[App\Resource::TYPE_HYPERLINK])
    <a href="{{ $resource->resource_url }}">{{ $resource->resource_url }}</a>
@else
    @if (empty($data))
        <div class="col-sm-12 m-t-lg text-center">{{ __('custom.no_info') }}</div>
    @else
        @if (
            $resource->format_code == App\Resource::FORMAT_CSV
            || $resource->format_code == App\Resource::FORMAT_TSV
            || $resource->format_code == App\Resource::FORMAT_ODS
            || $resource->format_code == App\Resource::FORMAT_SLK
        )
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
        @elseif ($resource->format_code == App\Resource::FORMAT_XML)
            <textarea
                class="js-xml-prev col-xs-12 m-b-md"
                data-xml-data="{{ $data }}"
                rows="20"
            ></textarea>
        @elseif ($resource->format_code == App\Resource::FORMAT_JSON && isset($data->text))
            <p>@php echo nl2br(e($data->text)) @endphp</p>
        @elseif (
                $resource->format_code == App\Resource::FORMAT_XSD
                || $resource->format_code == App\Resource::FORMAT_RTF
                || $resource->format_code == App\Resource::FORMAT_ODT
        )
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
                name="name"
                type="text"
                value="{{ $resource->name }}"
            >
            <input
                hidden
                name="format"
                type="text"
                value="{{ $resource->file_format }}"
            >
            <button
                name="download"
                type="submit"
                class="badge badge-pill pull-right js-ga-event"
                data-ga-action="download"
                data-ga-label="resource download"
                data-ga-category="data"
            >{{ uctrans('custom.download') }}</button>
        </form>
    @endif
@endif


