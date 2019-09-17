<?php

namespace T2G\Common\Observers;

use T2G\Common\Models\Payment;

class PaymentObserver
{
    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    public function saving(Payment $payment)
    {
        $payment->status_code = Payment::getPaymentStatus($payment);
    }
}
