<div id="ccuPeakChart" style="width: 98%;height: 450px;padding: 1% 0"></div>
<div class="clearfix"></div>
@push('extra-js')
    <script>
        Highcharts.chart('ccuPeakChart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Thống kê CCU đỉnh theo ngày'
            },
            xAxis: {
                categories: {!! json_encode($peakChart['xAxisData']) !!},
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'User online'
                },
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.time})<br/>',
                shared: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                },
                series: {
                    dataLabels: {
                        enabled: true,
                        inside: true
                    }
                }
            },
            series: [
                @foreach($peakChart['yAxisData'] as $name => $data)
                {
                    dataLabels: [
                        {
                        // align: 'left',
                            rotation: 90,
                            format: '{point.time}',
                        },
                        {
                            verticalAlign: 'top',
                            format: '{y}',
                        }
                    ],
                    name: '{{ $name }}',
                    data: [
                        @foreach($data as $point)
                        {
                            y: {{ intval($point['value']) }},
                            time: '{{ $point['time'] }}',
                        },
                        @endforeach
                    ]
                },
                @endforeach
            ]
        });
    </script>
@endpush
