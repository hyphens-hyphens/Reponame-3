<div id="ccuTimeSeriesChart" style="width: 100%;height: 450px;"></div>
@push('extra-js')
    <script type="text/javascript">
        Highcharts.chart('ccuTimeSeriesChart', {
            chart: {
                zoomType: 'x'
            },
            title: {
                text: 'Thống kê CCU theo thời gian'
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
                    // series: {
                    //     pointIntervalUnit: 'hour',
                    // }
                },
                series: {
                    pointStart: {{ $timeSeriesChart['pointStart']  }},
                    pointInterval: 300000,
                }
            },
            series: [
                    @foreach($timeSeriesChart['yAxisData'] as $server => $data)
                {
                    type: 'area',
                    name: '{{ $server }}',
                    data: {!! json_encode($data) !!}
                },
                @endforeach
            ]
        });
    </script>
@endpush
