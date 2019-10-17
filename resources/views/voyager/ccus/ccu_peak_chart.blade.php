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
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'User online'
                },
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                shared: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [
                @foreach($peakChart['yAxisData'] as $server => $data)
                {
                    name: '{{ $server }}',
                    data: {!! json_encode($data) !!}
                },
                @endforeach
            ]
        });
    </script>
@endpush
