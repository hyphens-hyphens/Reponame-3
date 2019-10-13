@extends('t2g_common::voyager.master')

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        @include('voyager::dimmers')
        <div class="widgets">
            @include('t2g_common::voyager.dashboard.widgets.user')
        </div>

    </div>
@stop
