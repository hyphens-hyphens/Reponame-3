<div class="col-xs-12 col-sm-8 col-md-8">
    <div class="panel widget widget-small ranking-widget">
        <h3 class="panel-heading text-center">
            <span class="voyager-medal-rank-star"></span> Bảng Xếp Hạng
        </h3>
        <div class="tab-server-rank">
            @foreach($serverInfo as $server)
                <h5 data-url="{{ route('voyager.ranking.top_level') }}" class="server-rank {{ $server }}" data-servername="{{ $server }}" >{{ $server }}</h5>
            @endforeach
        </div>
        <div class="panel-body rank-list-content-ad">
        </div>
        <div id="rank-loader">
            <img src="{{ asset('t2g_common/images/ajax-loader.gif') }}" alt="rank-loader">
        </div>
    </div>
</div>

