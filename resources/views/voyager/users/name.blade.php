{{ $data->name }}
@if($totalPaid = $data->getTotalPaid())
    <span class="label label-success"><i class="voyager-dollar"></i> {{ number_format($totalPaid / 1000) }}K</span>
@endif
