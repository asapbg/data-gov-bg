@if (isset($resPagination))
    <div class="row">
        <div class="col-xs-12 col-sm-4" style="padding-top: 30px;">
            @if (
                $resPagination instanceof Illuminate\Pagination\LengthAwarePaginator
                && !empty($resPagination->items())
            )
                @php
                    if($resPagination->onFirstPage()) {
                        $resPaginationFrom = $resPagination->items()['from'];
                        $resPaginationTo = $resPagination->items()['to'] - 1;
                    } else {
                        $resPaginationFrom = $resPagination->items()['from'] - 1;
                        $resPaginationTo = $resPagination->items()['to'] - 2;
                    }
                @endphp
                {{ sprintf(
                    __('custom.resource_pagination_info'),
                    $resPaginationFrom,
                    $resPaginationTo,
                    $resPagination->total() - 1
                ) }}
            @endif
        </div>
        <div class="col-xs-12 col-sm-8 text-right">
            {{ $resPagination->render() }}
        </div>
    </div>
@endif
