<?php

namespace T2G\Common\Contract;

use T2G\Common\Util\MobileCard;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

/**
 * Class CardPaymentInterface
 */
interface CardPaymentInterface
{
    const PARTNER_RECARD      = 'recard';
    const PARTNER_NAPTHENHANH = 'napthenhanh';

    /**
     * @return string
     */
    public function getPartnerName();
    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * @param MobileCard $card
     * @param string               $paymentId
     *
     * @return CardPaymentResponseInterface|null
     */
    public function useCard(MobileCard $card, $paymentId = '');

    /**
     * @param $callbackCode
     *
     * @return string
     */
    public function getCallbackMessage($callbackCode);

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    public function getTransactionCodeFromCallback(Request $request);

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array [$status, $amount, $callbackCode]
     */
    public function parseCallbackRequest(Request $request);

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function logCallbackRequest(Request $request);

    /**
     * @param                          $message
     *
     * @return void
     */
    public function logCallbackProcessed($message);

    /**
     * @param CardPaymentResponseInterface $result
     *
     * @return void
     */
    public function logCardPaymentError(CardPaymentResponseInterface $result);
}
