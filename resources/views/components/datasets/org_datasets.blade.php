@php $root = empty($admin) ? 'user' : 'admin'; @endphp
<div class="row">
    @if ((!empty($admin)) || !empty($buttons['add']))
        <div class="col-sm-3 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn">
                <a
                    href="{{ url('/'. $root .'/organisations/dataset/create/'. $organisation->uri) }}"
                >{{ __('custom.add_new_dataset') }}</a>
            </span>
        </div>
    @endif
    @if ((!empty($admin)) || !empty($buttons['view']))
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field p-l-lg">
            <form method="GET">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control js-ga-event"
                    placeholder="{{ __('custom.search') }}"
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                    data-ga-action="search"
                    data-ga-label="data search"
                    data-ga-category="data"
                >
            </form>
        </div>
    @endif
</div>
<div class="row">
    <div class="col-sm-3 col-xs-12 text-left p-l-none">
        @include('partials.org-info', ['organisation' => $organisation])
    </div>
    <div class="col-sm-9 col-xs-12 m-t-md">
        <div class="row">
            <div class="articles m-t-lg">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        <div class="article m-b-lg col-xs-12 user-dataset">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-sm-12 p-l-none">
                                <a href="{{ url('/'. $root .'/organisations/dataset/view/'. $set->uri) }}">
                                    <h2 class="m-t-xs">{{ $set->name }}</h2>
                                </a>
                                <div class="desc">
                                    {!! nl2br(truncate(e($set->descript), 150)) !!}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                    @if ((!empty($admin)) || (!empty($buttons[$set->uri]['edit'])))
                                        <div class="col-xs-6">
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a
                                                    href="{{ url('/'. $root .'/organisations/'. $organisation->uri .'/dataset/edit/'. $set->uri) }}"
                                                >{{ uctrans('custom.edit') }}</a>
                                            </span>
                                        </div>
                                    @endif
                                    <div class="col-xs-6">
                                        <form method="POST">
                                            {{ csrf_field() }}
                                            @if ((!empty($admin)) || (!empty($buttons[$set->uri]['delete'])))
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        class="badge badge-pill m-b-sm del-btn"
                                                        type="submit"
                                                        name="delete"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                            @endif
                                            <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                        </form>
                                    </div>
                                    </div>
                                    <div class="pull-right">
                                        <span>
                                            <a
                                                href="{{ url('/'. $root .'/organisations/dataset/view/'. $set->uri) }}"
                                            >{{ __('custom.see_more') }}</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
