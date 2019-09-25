{{ $data->name }}
@if($totalPaid = $data->getTotalPaid())
    <span class="label label-success" data-toggle="tooltip" title="Đã nạp"><i class="voyager-dollar"></i> {{ number_format($totalPaid / 1000) }}K</span>
@endif
@if($totalDebt = $data->getTotalDebt())
    <span class="label label-danger" data-toggle="tooltip" title="Đang ứng"><i class="voyager-dollar"></i> {{ number_format($totalDebt / 1000) }}K</span>
@endif
