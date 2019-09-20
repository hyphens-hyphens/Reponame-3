<p class="h4">
    <span class="label label-dark">#{{ $item->id }}</span>
    @if($item->amount > 0)
    <span class="label label-success"><i class="voyager-dollar"></i> {{ number_format($item->amount / 1000) }}K</span>
    @endif

</p>
<p class="h4">
    <span class="label label-danger"><i class="{{ \T2G\Common\Util\CommonHelper::getIconForPaymentType($item->payment_type) }}"></i>
        {{ \T2G\Common\Models\Payment::displayPaymentType($item->payment_type) }}
    </span>
</p>
@if($item->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_BANK_TRANSFER)
    <p>
        {{ $item->pay_from }}
    </p>
@endif

@if($item->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_CARD)
    @include('t2g_common::voyager.payments.card_info')
@endif
@if($item->note)
    <p><i class="voyager-info-circled"></i> {{ $item->note }}</p>
@endif
