<rss version="2.0">
    @if (!empty($history))
        <channel>
            @foreach ($history as $singleNews)
                <item>
                    <news id="{{ $singleNews->id }}">
                        <title>{{ $singleNews->title }}</title>
                        <link>{{ url('news/view/'. $singleNews->id) }}</link>
                        <description>{{ str_replace("&nbsp;", '&#160;', $singleNews->abstract) }}</description>
                        <moment>{{ $singleNews->created_at }}</moment>
                        <guid>{{ $singleNews->id }}</guid>
                    </news>
                </item>
            @endforeach
        </channel>
    @endif
</rss>
