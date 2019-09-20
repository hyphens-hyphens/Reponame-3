@extends('voyager::master')

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="panel widget">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 text-center">
                    <span class="h4">Thống kê nhanh</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4">
                    <table class="table table-bordered">
                        <thead>
                        <th width="100"></th>
                        <th>Doanh thu</th>
                        <th>Lợi nhuận</th>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Hôm nay</td>
                            <td>{{ number_format($todayRevenue['total']) }}</td>
                            <td><span class="h5 label label-success">{{ number_format($todayRevenue['revenue']) }}</span></td>
                        </tr>
                        <tr>
                            <td>Tháng này</td>
                            <td>{{ number_format($thisMonthRevenue['total']) }}</td>
                            <td><span class="h5 label label-success">{{ number_format($thisMonthRevenue['revenue']) }}</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row report-registered">
                <div class="col-xs-12">
                    <form action="" id="formReportPayment">
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label for="reportRange" class="control-label">
                                    Chọn thời gian thống kê
                                </label>
                            </div>
                            <div class="col-xs-9">
                                <div class="form-group">
                                    @include('admin.partials.input_daterange')
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <div class="form-group">
                                    <button type="submit" id="btnReportPayment" class="btn btn-info" style="margin-top: 0">Report</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xs-12" id="dashboardReport">
                    <div>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active table-default">
                                <a href="#revenue_chart" aria-controls="revenue_chart" role="tab" data-toggle="tab">Biểu đồ</a>
                            </li>
                            {{--<li role="presentation">--}}
                            {{--<a href="#users_paid_chart" aria-controls="users_paid_chart" role="tab" data-toggle="tab">Nguồn đăng ký</a>--}}
                            {{--</li>--}}
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="revenue_chart">
                                @include('admin.payments.revenue_chart')
                            </div>
                            {{--<div role="tabpanel" class="tab-pane" id="users_paid_chart">--}}
                            {{--@include('admin.payments.users_paid_chart')--}}
                            {{--</div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
