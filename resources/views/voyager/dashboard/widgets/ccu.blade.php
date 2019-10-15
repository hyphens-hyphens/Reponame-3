<div class="col-xs-12 col-sm-4">
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
                    <div class="h5 col-xs-6" style="margin-top: 0;">
                        {{ \Illuminate\Support\Str::words($server, 2, '') }}: <span class="h5 ccu-count label label-success" data-server="{{ $server }}">{{ number_format($ccu) }}</span>
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
            // legend: {
            //     enabled: false
            // },
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
                xDateFormat: '%H:00 %d-%m',
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
                    // series: {
                    //     pointIntervalUnit: 'hour',
                    // }
                },
                series: {
                    pointInterval: 3600000,
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
        let ccuUpdateInterval = setInterval(function () {
            $.ajax({
                url: '{{ route('voyager.ccus.tick') }}',
                success: function (result) {
                    console.log(result);
                    for (var property in result) {
                        let $ccuCount = $('.ccu-count[data-server="' + property + '"]');
                        $ccuCount.text(parseInt(result[property]).toLocaleString())
                            .removeClass('label-danger')
                            .addClass('label-success')
                            .addClass('blinking-text')
                        ;
                        setTimeout(function () {
                            $ccuCount.removeClass('blinking-text');
                        }, 900)
                    }
                },
                dataType: 'JSON',
                error: function () {
                    $('.ccu-count').addClass('label-danger').removeClass('label-success').text("Error");
                }
            });
        }, 3000);
    </script>
@endpush