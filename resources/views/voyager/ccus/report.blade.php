@extends('voyager::master')

@section('page_title', "CCU Report")

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-activity"></i> CCU Report
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="panel widget">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 text-center">
                    <span class="h4">CCU hiện tại</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4">
                    <table class="table table-bordered">
                        <thead>
                        <th>Server</th>
                        <th>CCU</th>
                        </thead>
                        <tbody>
                            @foreach($ccus as $server => $ccu)
                            <tr>
                                <td>{{ $server }}</td>
                                <td>{{ number_format($ccu) }}</td>
                            </tr>
                            @endforeach
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
                                    @include('t2g_common::voyager.partials.input_daterange')
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
                <div class="col-xs-12">
                    @include('t2g_common::voyager.ccus.ccu_chart')
                </div>
            </div>
        </div>
    </div>
@stop
