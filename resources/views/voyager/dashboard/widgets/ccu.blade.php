<div class="col-xs-12 col-sm-6 col-md-4">
    <div class="panel widget widget-small">
        <a class="widget-goto" href="{{ route('voyager.ccus.report') }}" data-toggle="tooltip" title="Xem chi tiết">
            <span class="voyager-forward"></span>
        </a>
        <h3 class="panel-heading text-center">
            <span class="voyager-activity"></span> CCU
        </h3>
        <div class="panel-body">
            <div data-toggle="tooltip" title="CCU hiện tại, tự cập nhật mỗi 3s">
                @foreach($ccus as $server => $ccu)
                    <div class="h5 col-xs-12 col-sm-6" style="margin-top: 0;">
                        {{ \Illuminate\Support\Str::words($server, 2, '') }}: <span class="h6 ccu-count label label-success" data-server="{{ $server }}">{{ number_format($ccu) }}</span>
                    </div>
                @endforeach
            </div>
            <div id="ccuChart" style="width: 100%; height: 200px;"></div>
        </div>
    </div>
</div>

@push('extra-js')
    <script>
        Highcharts.chart('ccuChart', {
            title: {
                text: ''
            },
            time: {
                timezoneOffset: -420
            },
            xAxis: {
                type: 'datetime',
            },
            yAxis: {
                min: 0,
                title: {
                    text: null
                },
                // visible: false
            },
            tooltip: {
                // pointFormat: '<b>{point.y}</b><br/>',
                xDateFormat: '%H:%M %d-%m',
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null,

                },
                series: {
                    pointStart: {{ $chart['pointStart'] }}, // milisecond timestamp
                    pointInterval: 300000, // 1 hour
                }
            },
            series: [
                @foreach($chart['yAxisData'] as $server => $data)
                {
                    type: 'area',
                    name: '{{ $server }}',
                    data: {!! json_encode($data) !!}
                },
                @endforeach
            ]
        });
        let ccuUpdateTimeout = '';
        let refresh = function () {
            $.ajax({
                url: '{{ route('voyager.ccus.tick') }}',
                success: function (result) {
                    for (var property in result) {
                        let $ccuCount = $('.ccu-count[data-server="' + property + '"]');
                        $ccuCount.text(parseInt(result[property]).toLocaleString())
                            .removeClass('label-danger')
                            .addClass('label-success')
                            .addClass('blinking-text')
                        ;
                        setTimeout(function () {
                            $ccuCount.removeClass('blinking-text');
                        }, 900);
                        clearTimeout(ccuUpdateTimeout);
                        ccuUpdateTimeout = setTimeout(refresh, {{ config('t2g_common.game_api.ccu_tick_interval', 3000) }});
                    }
                },
                dataType: 'JSON',
                error: function () {
                    $('.ccu-count').addClass('label-danger').removeClass('label-success').text("Error");
                    clearTimeout(ccuUpdateTimeout);
                    ccuUpdateTimeout = setTimeout(refresh, 30000);
                }
            });
        };

        refresh();
    </script>
@endpush
