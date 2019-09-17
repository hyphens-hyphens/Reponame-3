<?php

namespace T2G\Common\Exceptions;

use T2G\Common\Models\Payment;

/**
 * Class PaymentApiException
 */
class PaymentApiException extends \Exception
{
    const GAME_PAYMENT_API_ERROR_CODE = 1;
    /**
     * @var
     */
    protected $payment;

    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    public function setPaymentItem(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return mixed
     */
    public function getPaymentItem()
    {
        return $this->payment;
    }
}
