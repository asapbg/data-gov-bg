@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @if (Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'group'])
        @else
            @include('partials.user-nav-bar', ['view' => 'group'])
        @endif
        @include('partials.group-nav-bar', ['view' => 'datasets', 'group' => $group])
        @if (isset($dataset->name))
            <div class="row">
                <div class="col-sm-3 col-xs-12 sidenav">
                    @include('partials.group-info', ['group' => $group])
                </div>
                <div class="col-sm-9 col-xs-12">
                    <div class="col-sm-12 user-dataset m-l-10">
                        <h2>{{ $dataset->name }}</h2>
                        <div class="col-sm-12 p-l-none m-b-lg">
                            <div class="tags pull-left">
                                @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                    @foreach ($dataset->tags as $tag)
                                        <span class="badge badge-pill m-b-sm">{{ $tag->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <p>
                            <strong>{{ utrans('custom.status') }}:</strong>
                            @if ($dataset->status == App\DataSet::STATUS_DRAFT)
                                &nbsp;<span>{{ utrans('custom.draft') }}</span>
                            @else
                                &nbsp;<span>{{ utrans('custom.published') }}</span>
                            @endif
                        </p>
                        <p>
                            <strong>{{ __('custom.visibility') }}:</strong>
                            @if ($dataset->visibility == App\DataSet::VISIBILITY_PUBLIC)
                                &nbsp;<span>{{ utrans('custom.public') }}</span>
                            @else
                                &nbsp;<span>{{ utrans('custom.private') }}</span>
                            @endif
                        </p>
                        <p>
                            <strong>{{ __('custom.version') }}:</strong>
                            &nbsp;{{ $dataset->version }}
                        </p>

                        <p>
                            <strong>{{ __('custom.source') }}:</strong>
                            &nbsp;{{ $dataset->source }}
                        </p>
                        <p>
                            <strong>{{ __('custom.author') }}:</strong>
                            &nbsp;{{ $dataset->author_name }}
                        </p>

                        <p>
                            <strong>{{ __('custom.contact_author') }}:</strong>
                            &nbsp;{{ $dataset->author_email }}
                        </p>

                        <p>
                            <strong>{{ __('custom.contact_support_name') }}:</strong>
                            &nbsp;{{ $dataset->support_name }}
                        </p>

                        <p>
                            <strong>{{ __('custom.contact_support') }}:</strong>
                            &nbsp;{{ $dataset->support_email }}
                        </p>
                        <p><strong>{{ __('custom.description') }}:</strong></p>
                        <div class="m-b-sm">
                            {!! nl2br($dataset->description) !!}
                        </div>
                        <p><strong>{{ __('custom.sla_agreement') }}:</strong></p>
                        <div class="m-b-sm">
                            {!! nl2br($dataset->sla) !!}
                        </div>
                        @if (
                            isset($dataset->custom_settings[0])
                            && !empty($dataset->custom_settings[0]->key)
                        )
                            <p><b>{{ __('custom.additional_fields') }}:</b></p>
                            @foreach ($dataset->custom_settings as $field)
                                <div class="row m-b-lg">
                                    <div class="col-xs-6">{{ $field->key }}</div>
                                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                </div>
                            @endforeach
                        @endif
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="pull-left history m-b-md">
                                @foreach ($resources as $resource)
                                    @if($buttons['seeResource'])
                                        <div class="{{ $resource->reported ? 'signaled' : '' }}">
                                            <a href="{{ url('/user/group/'. $group->uri .'/resource/'. $resource->uri) }}">
                                                <span>
                                                    <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                                </span>
                                                <span class="version-heading">{{ utrans('custom.resource') }}</span>
                                                <span class="version">&nbsp;&#8211;&nbsp;{{ $resource->name }}</span>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="info-bar-sm col-sm-7 col-xs-12 m-t-sm m-l-10">
                        <ul class="p-l-none">
                            <li>{{ __('custom.created_at') }}: {{ $dataset->created_at }}</li>
                            <li>{{ __('custom.created_by') }}: {{ $dataset->created_by }}</li>
                            <li>{{ __('custom.updated_at') }}: {{ $dataset->updated_at }}</li>
                            <li>{{ __('custom.updated_by') }}: {{ $dataset->updated_by }}</li>
                        </ul>
                    </div>
                    <div class="col-sm-9 col-xs-12 m-b-lg">
                        @if ($buttons['addResource'])
                            <a
                                class="btn btn-primary m-r-md m-l-sm"
                                href="{{ route('groupResourceCreate', ['uri' => $dataset->uri, 'grpUri' => $group->uri]) }}"
                            >{{ uctrans('custom.add_resource') }}</a>
                        @endif
                        @if ($buttons[$dataset->uri]['edit'])
                            <a
                                class="btn btn-primary m-r-md"
                                href="{{ url('/user/group/'. $group->uri .'/dataset/edit/'. $dataset->uri) }}"
                            >{{ uctrans('custom.edit') }}</a>
                        @endif
                        @if ($buttons[$dataset->uri]['delete'])
                            <form method="POST" class="inline-block">
                                {{ csrf_field() }}
                                <button
                                    class="btn del-btn btn-primary m-r-md"
                                    type="submit"
                                    name="delete"
                                    data-confirm="{{ __('custom.remove_data') }}"
                                >{{ uctrans('custom.remove') }}</button>
                                <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
