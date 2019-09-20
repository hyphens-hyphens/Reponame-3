@if($data->gamecoin == 0)
    <span class="badge badge-danger">Phí support</span>
@else
    <span class="badge badge-default">{{ number_format($data->gamecoin) }}</span>
@endif
