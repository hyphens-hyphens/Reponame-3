<div class="col-xs-12 col-sm-4">
    <div class="panel widget widget-small">
        <a class="widget-goto" href="{{ route('voyager.payments.report') }}" data-toggle="tooltip" title="Xem chi tiết">
            <span class="voyager-forward"></span>
        </a>
        <h3 class="panel-heading text-center">
            <span class="voyager-dollar"></span> Doanh thu
        </h3>
        <div class="panel-body">
            <div class="h4 text-center">
                <span class="text-info" data-toggle="tooltip" title="Doanh thu hôm nay">
                    {{ number_format($todayRevenue['total']) }}<sup>đ</sup>
                </span>
                <span class="voyager-dot-3"></span>
                <span class="text-success" data-toggle="tooltip" title="Doanh thu tháng này">
                    {{ number_format($thisMonthRevenue['total']) }}<sup>đ</sup>
                </span>
            </div>
            <div id="revenueChart" style="width: 100%; height: 200px;"></div>
        </div>
    </div>
</div>

@push('extra-js')
    <script>
        Highcharts.chart('revenueChart', {
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
                pointFormat: '<b>{point.y}</b><sup>đ</sup><br/>',
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
                    name: 'Đ',
                    data: {!! json_encode($chart['yAxisData']) !!}
                }
            ]
        });
    </script>
@endpush
