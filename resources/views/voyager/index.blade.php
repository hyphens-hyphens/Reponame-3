@extends('t2g_common::voyager.master')

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        @include('voyager::dimmers')
        <div class="t2g-widgets">
            {!! $widgetUser !!}
            {!! $widgetPayment !!}
            {!! $widgetCCU !!}
            <div class="clearfix"></div>
    </div>

    </div>
@stop
