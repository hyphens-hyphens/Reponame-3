@extends('t2g_common::voyager.master')

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        @include('voyager::dimmers')
        @include('t2g_common::voyager.dashboard.widget_user')
    </div>
@stop
