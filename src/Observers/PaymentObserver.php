<?php

namespace T2G\Common\Observers;

use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Models\Payment;
use T2G\Common\Util\MobileCard;

class PaymentObserver
{
    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    public function saving(Payment $payment)
    {
        $payment->status_code = Payment::getPaymentStatus($payment);
        $this->setPayMethod($payment);
        $this->setProfit($payment);
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    private function setPayMethod(Payment $payment)
    {
        if (Payment::PAYMENT_TYPE_CARD == $payment->payment_type) {
            // pay_method of card payment should be implicit set previously
            return;
        } elseif(Payment::PAYMENT_TYPE_MOMO == $payment->payment_type) {
            $payment->pay_method = Payment::PAY_METHOD_MOMO;
        } elseif(Payment::PAYMENT_TYPE_BANK_TRANSFER == $payment->payment_type) {
            $payment->pay_method = Payment::PAY_METHOD_BANK_TRANSFER;
        } elseif(Payment::PAYMENT_TYPE_ADVANCE_DEBT == $payment->payment_type) {
            $payment->pay_method = Payment::PAY_METHOD_ADVANCE_DEBT;
        }
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    private function setProfit(Payment $payment)
    {
        if ($payment->status && $payment->pay_method != Payment::PAY_METHOD_ADVANCE_DEBT) {
            $profitRate = Payment::getProfitRate($payment->pay_method);
            $payment->profit = $payment->amount * $profitRate;
        }
    }

}
