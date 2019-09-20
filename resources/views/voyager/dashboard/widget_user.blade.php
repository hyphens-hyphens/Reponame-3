@php
extract($registeredChart);
@endphp
<div class="panel widget">
    <div class="row">
        <div class="col-md-12">
            <div>
                - Số tài khoản đăng ký trong ngày:
                <strong style="font-size: 16px;">{{ $regToday }}</strong>
            </div>
            <div>
                - Tổng số tài khoản: <strong style="font-size: 16px;">{{ number_format($regTotal, 0, '', '.') }}</strong>
            </div>
        </div>
    </div>
    <div class="row report-registered">
        <form action="" id="formReportRegistered" class="form-horizontal">
            <div class="col-xs-12">
                <div class="form-group">
                    <label for="reportRange" class="col-sm-3 control-label">
                        Chọn thời gian thống kê
                    </label>
                    <div class="col-sm-4">
                        <div class="form-group">
                            @include('t2g_common::voyager.partials.input_daterange')
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <button type="submit" id="btnReportRegistered" class="btn btn-info" style="margin-top: 0">Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-xs-12" id="dashboardReport">
            <div>
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active table-default">
                        <a href="#registered_chart" aria-controls="registered_chart" role="tab" data-toggle="tab">Biểu đồ</a>
                    </li>
                    <li role="presentation">
                        <a href="#utm_report" aria-controls="utm_report" role="tab" data-toggle="tab">Nguồn đăng ký</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="registered_chart">
                        @include('t2g_common::voyager.dashboard.registered_chart')
                    </div>
                    <div role="tabpanel" class="tab-pane" id="utm_report">
                        @include('t2g_common::voyager.dashboard.utm_report')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

