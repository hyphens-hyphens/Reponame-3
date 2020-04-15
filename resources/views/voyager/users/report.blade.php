@extends('t2g_common::voyager.master')

@section('page_title', "Users Report")

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-people"></i> Users Report
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @php
            extract($registeredChart);
        @endphp
        <div class="panel widget">
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
                    <div><b style="font-size: 16px">NRU: <span class="h5 label label-info">{{ $nru }}</span></b></div>
                    <hr>
                    <div>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs nav-" role="tablist">
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
                                @include('t2g_common::voyager.users.registered_chart')
                            </div>
                            <div role="tabpanel" class="tab-pane" id="utm_report">
                                @include('t2g_common::voyager.users.utm_report')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


