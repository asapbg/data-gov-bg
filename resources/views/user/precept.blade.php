<div class="row">
    <div class="col-sm-3 col-xs-12">
        @if ($organisation->type == App\Organisation::TYPE_GROUP)
            @include('partials.group-info', ['group' => $organisation])
        @else
            @include('partials.org-info', ['organisation' => $organisation])
        @endif
    </div>
    <div class="col-sm-9 col-xs-12 p-md">
        @if(\Auth::user()->is_admin)
            @if ($precepts)
                @foreach($precepts as $precept)
                    <div class="col-xs-4 p-sm">
                        {{ $precept['name'] }}
                    </div>
                    <div class="col-xs-8 p-sm">
                        <a href="{{ $precept['precept']['path'] }}" target="_blank" id="precept_file">
                            <i class="fa fa-file-pdf-o red"></i> {{ $precept['precept']['name'] }}
                        </a>
                    </div>
                    <br>
                    <br>
                @endforeach
            @else
                <div class="col-sm-9 m-t-xl no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
        @endif
    </div>
</div>
