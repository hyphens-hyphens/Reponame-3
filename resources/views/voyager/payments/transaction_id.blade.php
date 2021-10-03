<p class="h4">
    @php
        if ($data->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_ADVANCE_DEBT)
           $paymentTypeIconClass   = 'label-danger';
        elseif($data->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_ADD_XU_CTV)
           $paymentTypeIconClass   = 'label-warning';
        elseif($data->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_TRAO_THUONG_XU_CTV)
           $paymentTypeIconClass   = 'label-primary';
        else
           $paymentTypeIconClass   = 'label-info';
    @endphp
    <span class="label {{ $paymentTypeIconClass }}">
        <i class="{{ \T2G\Common\Util\CommonHelper::getIconForPaymentType($data->payment_type) }}"></i>
        {{ \T2G\Common\Models\Payment::displayPaymentType($data->payment_type) }}
    </span>
    @if($data->amount > 0)
        &nbsp;
        <span class="label label-success"><i class="voyager-dollar"></i>
        {{ number_format($data->amount / 1000) }}K
    </span>
    @endif
</p>
@if($data->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_BANK_TRANSFER)
    <p>
        {{ $data->pay_from }}
    </p>
@endif

@if($data->payment_type == \T2G\Common\Models\Payment::PAYMENT_TYPE_CARD)
    @include('t2g_common::voyager.payments.card_info', ['item' => $data])
@endif
@if($data->note)
    <p><i class="voyager-info-circled"></i> {{ $data->note }}</p>
@endif
