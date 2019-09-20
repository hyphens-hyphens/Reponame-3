<!-- Few Dynamic Styles -->
<style type="text/css">
    .voyager .side-menu .navbar-header {
        background:{{ config('voyager.primary_color','#22A7F0') }};
        border-color:{{ config('voyager.primary_color','#22A7F0') }};
    }
    .widget .btn-primary{
        border-color:{{ config('voyager.primary_color','#22A7F0') }};
    }
    .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
        background:{{ config('voyager.primary_color','#22A7F0') }};
    }
    .voyager .breadcrumb a{
        color:{{ config('voyager.primary_color','#22A7F0') }};
    }
    .table, .form-control, label{
        color: black;
    }
</style>
