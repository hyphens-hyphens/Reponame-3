@php
    extract($widgetUser);
@endphp
<div class="col-xs-12 col-sm-4">
    <div class="panel widget">
        <a class="widget-goto" href="{{ route('voyager.users.report') }}" data-toggle="tooltip" title="Xem chi tiết">
            <span class="voyager-forward"></span>
        </a>
        <h3 class="panel-heading text-center">
            <span class="voyager-people"></span> Đăng ký mới
        </h3>
        <div class="panel-body">
            <div class="h4 text-center">
                Hôm nay: <span class="text-info" data-toggle="tooltip" title="Trực tiếp">{{ $todayDirectRegistered }}</span> /
                <span class="text-warning" data-toggle="tooltip" title="Marketing">{{ $todayPaidRegistered }}</span>
            </div>
            <div class="report-registered">
                <div id="registerChart" style="width: 100%; height: 200px;"></div>
            </div>
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
    <style>
        .highcharts-credits {display: none}
        .widget-goto {
            font-size: 16px;
            position: absolute;
            top: 13px;
            right: 15px;
            float: right;
        }
        .panel.widget {
            padding: 20px 30px;
        }
    </style>
@endpush
