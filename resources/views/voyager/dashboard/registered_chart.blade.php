<div id="registerChart" style="width: 98%;height: 450px;padding: 1% 0"></div>
<div class="clearfix"></div>
@push('extra-js')
    <script>
        Highcharts.chart('registerChart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Thống kê User đăng ký theo ngày'
            },
            xAxis: {
                categories: {!! json_encode($dateArray) !!}
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'User đăng ký'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                    }
                }
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                shared: true
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                    }
                }
            },
            series: [{
                name: 'Direct',
                data: {!! json_encode($registeredChartData['direct']) !!}
            }, {
                name: 'MKT',
                data: {!! json_encode($registeredChartData['mkt']) !!}
            }]
        });
    </script>
@endpush
