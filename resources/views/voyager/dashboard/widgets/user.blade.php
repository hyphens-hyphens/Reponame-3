<div class="col-xs-12 col-sm-4">
    <div class="panel widget widget-small">
        <a class="widget-goto" href="{{ route('voyager.users.report') }}" data-toggle="tooltip" title="Xem chi tiết">
            <span class="voyager-forward"></span>
        </a>
        <h3 class="panel-heading text-center">
            <span class="voyager-people"></span> Đăng ký mới
        </h3>
        <div class="panel-body">
            <div class="h4 text-center">
                Hôm nay: <span class="text-info" data-toggle="tooltip" title="Trực tiếp">{{ number_format($todayDirectRegistered) }}</span> /
                <span class="text-warning" data-toggle="tooltip" title="Marketing">{{ number_format($todayPaidRegistered) }}</span>
            </div>
            <div id="registerChart" style="width: 100%; height: 200px;"></div>
        </div>
    </div>
</div>

@push('extra-js')
    <script>
        Highcharts.chart('registerChart', {
            chart: {
                type: 'line'
            },
            legend: {
                enabled: false
            },
            title: {
                text: ''
            },
            xAxis: {
                categories: {!! json_encode($chart['xAxisData']) !!},
                visible: false
            },
            yAxis: {
                min: 0,
                title: {
                    text: null
                },
                // visible: false
            },
            tooltip: {
                pointFormat: '<b>{point.y}</b> tài khoản<br/>',
            },
            plotOptions: {
                column: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            series: [
                {
                    name: 'Lượt đăng ký',
                    data: {!! json_encode($chart['yAxisData']) !!}
                }
            ]
        });
    </script>
@endpush
