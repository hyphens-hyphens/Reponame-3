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
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    private function setPayMethod(Payment $payment)
    {
        if (Payment::PAYMENT_TYPE_CARD == $payment->payment_type) {
            if ($payment->card_type == MobileCard::TYPE_ZING) {
                $payment->pay_method = Payment::PAY_METHOD_ZING_CARD;
            } else {
                // do not set pay_method again if its value already was a valid card payment pay_method
                if (in_array($payment->pay_method, [Payment::PAY_METHOD_NAPTHENHANH, Payment::PAY_METHOD_RECARD])) {
                    return;
                }
                /** @var CardPaymentInterface $paymentService */
                $paymentService = app(CardPaymentInterface::class);
                $payment->pay_method = $paymentService->getPartnerName() == CardPaymentInterface::PARTNER_NAPTHENHANH ? Payment::PAY_METHOD_NAPTHENHANH : Payment::PAY_METHOD_RECARD;
            }
        } elseif(Payment::PAYMENT_TYPE_MOMO == $payment->payment_type) {
            $payment->pay_method = Payment::PAY_METHOD_MOMO;
        } elseif(Payment::PAYMENT_TYPE_BANK_TRANSFER == $payment->payment_type) {
            $payment->pay_method = Payment::PAY_METHOD_BANK_TRANSFER;
        } elseif(Payment::PAYMENT_TYPE_ADVANCE_DEBT == $payment->payment_type) {
            $payment->pay_method = Payment::PAY_METHOD_ADVANCE_DEBT;
        }
    }

}
