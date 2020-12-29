<div class="col-xs-12 col-sm-6 col-md-6">
    <div class="panel widget widget-small">
        <h3 class="panel-heading text-center">
            <span class="voyager-medal-rank-star"></span> Bảng Xếp Hạng
        </h3>
        <div class="tab-server-rank">
            @foreach($serverInfo as $key => $server)
                <h5 data-url="{{ route('voyager.ranking.top_level') }}" class="server-rank {{ $server['widget'] }}" data-servername="{{ $server['widget'] }}" >{{ strtoupper($key) }}-{{ $server['name'] }}</h5>
            @endforeach
        </div>
        <div class="panel-body rank-list-content-ad">
        </div>
        <div id="rank-loader">
            <img src="{{ asset('images/assets/ajax-loader.gif') }}" alt="rank-loader">
        </div>
    </div>
</div>