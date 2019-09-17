<?php

namespace T2G\Common\Action;

use T2G\Common\Models\Payment;
use TCG\Voyager\Actions\AbstractAction;

/**
 * Class AcceptPaymentAction
 *
 * @package \T2G\Common\Action
 */
class RejectPaymentAction extends AbstractAction
{

    public function getTitle()
    {
        return "Reject";
    }

    public function getIcon()
    {
        return "voyager-x";
    }

    public function getPaymentsRoute()
    {
        return route('voyager.payments.reject', [$this->data->id]);
    }

    public function getDefaultRoute()
    {
        return route('voyager.payments.browse');
    }

    public function shouldActionDisplayOnDataType()
    {
        /** @var \T2G\Common\Models\Payment $payment */
        $payment = $this->data;
        if ($this->dataType->slug != 'payments') {
            return false;
        }

        return Payment::isRejectable($payment);
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-danger btn-sm pull-right'
        ];
    }
}
