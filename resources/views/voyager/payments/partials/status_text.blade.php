@php
    $extended = $isAdmin ? "" : ". Vui lòng liên hệ BQT để được hỗ trợ.";
    $defaultClasses = $isAdmin ? "h5 label" : "label";
    $classes = $text = "";
    switch ($statusCode) {
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_SUCCESS:
            $classes = "{$defaultClasses} label-success c-green";
            $text = "Thành công";
            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_PROCESSING:
            $classes = "{$defaultClasses} label-primary c-green";
            $text = "Đang xử lý";
            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_MANUAL_ADD_GOLD_ERROR:
            $classes = "{$defaultClasses} label-danger c-red";
            $text = "Lỗi API nạp tiền";
            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_GATEWAY_RESPONSE_ERROR:
            $classes = "{$defaultClasses} label-danger c-red";
            $text = "Thẻ sai";
            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_GATEWAY_ADD_GOLD_ERROR:
            $classes = "{$defaultClasses} label-danger c-red";
            if ($isAdmin) {
                $text = "Đối tác phản hồi OK nhưng chưa add được vàng";
                $extraText = "Đối tác phản hồi OK! nhưng lỗi API nạp tiền. <br/>Có thể duyệt lại thẻ.";
            } else {
                $text = "Có lỗi xảy ra" . $extended;
            }

            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_NOT_SUCCESS:
            $classes = "{$defaultClasses} label-danger c-red";
            $text = "Không thành công";
            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_CARD_GATEWAY_NOT_ACCEPT:
            $classes = "{$defaultClasses} label-danger c-red";
            $text = $isAdmin ? "Đối tác không chấp nhận thẻ" : "Thẻ không hợp lệ";
            break;
        case \T2G\Common\Models\Payment::PAYMENT_STATUS_ADVANCE_DEBT_SUCCESS:
            $classes = "{$defaultClasses} label-success c-green";
            $text = $isAdmin ? "Ứng tiền thành công" : "Ứng tiền thành công";
            break;
    }
@endphp
<p><span class="{{ $classes }}">{{ $text }}</span></p>
@if($isAdmin && !empty($withExtraText) && !empty($extraText))
    <p>{!! $extraText !!}</p>
@endif
