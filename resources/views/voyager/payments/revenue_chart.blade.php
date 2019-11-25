@php
extract($revenue)
@endphp
<div class="row">
    <div class="col-xs-2 revenue-label">
        Tổng doanh thu:
    </div>
    <div class="col-xs-2">
        {{ number_format($total * 1000) }} VNĐ
    </div>
    <div class="col-xs-18">
        @foreach($seriesData as $name => $data)
            <span class="label label-info">{{ $name }}: {{  number_format(array_sum($data)) }}K</span>
        @endforeach
    </div>
</div>
<div class="row">
    <div class="col-xs-2 revenue-label">
        Tổng lợi nhuận:
    </div>
    <div class="col-xs-2">
        {{ number_format($totalRevenue * 1000) }} VNĐ
    </div>
</div>
<div class="row">
    <div class="col-xs-2 revenue-label">
        Pay Rate:
    </div>
    <div class="col-xs-10">
        <span class="label label-success" data-toggle="tooltip" data-title="Pay Users">{{ number_format($metrics['payUsers']) }}</span>
        &nbsp;/ <span class="label label-info" data-toggle="tooltip" data-title="Active Users">{{ number_format($metrics['activeUsers']) }}</span>
        &nbsp;= {{ number_format($metrics['payRate'] * 100, 2) }}%
    </div>
</div>
<div class="row">
    <div class="col-xs-2 revenue-label">
        ARPU:
    </div>
    <div class="col-xs-10">
        <span data-toggle="tooltip"
              data-title="Doanh thu">{{ number_format($revenue['total'] * 1000) }}
        </span>
        &nbsp;/ <span class="label label-success" data-toggle="tooltip"
                data-title="Pay Users">{{ number_format($metrics['payUsers']) }}
        </span>
        &nbsp;= {{ number_format($metrics['ARPU'], 2) }} VNĐ / user
    </div>
</div>

<div class="row">
    <div class="col-xs-2 revenue-label">
        ARPPU:
    </div>
    <div class="col-xs-10">
        <span data-toggle="tooltip"
              data-title="Doanh thu">{{ number_format($revenue['total'] * 1000) }}
        </span>
        &nbsp;/ <span class="label label-info" data-toggle="tooltip"
                data-title="Active Users">{{ number_format($metrics['activeUsers']) }}
        </span>
        &nbsp;= {{ number_format($metrics['ARPPU'], 2) }} VNĐ / user
    </div>
</div>
<div id="revenueChart" style="width: 98%;height: 450px;padding: 1% 0"></div>
@push('extra-js')
    <script type="text/javascript">
        Highcharts.chart('revenueChart', {
            chart: {
                type: 'column'
            },
            title: {
                text:'Thống kê doanh thu'
            },
            xAxis: {
                categories: [<?=$series?>],
                title:{
                    text: 'Ngày'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Số tiền người chơi nạp'
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
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}'
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
            series: [
                <?php
                $k = 0;
                foreach ($seriesData as $name => $data): ?>
                {
                    name: '<?=$name?>',
                    data: [<?=implode(',', $data)?>]
                }
                <?= $k++ < (count($seriesData) - 1) ? ',' : '' ?>
                <?php endforeach; ?>
            ]
        });
    </script>

    <style>
        .revenue-label {
            font-weight: bold;
        }
    </style>
@endpush

