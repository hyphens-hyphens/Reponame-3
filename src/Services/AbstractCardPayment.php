<?php

namespace T2G\Common\Services;

use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Contract\CardPaymentResponseInterface;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractCardPayment
 *
 * @package \T2G\Common\Services
 */
abstract class AbstractCardPayment implements CardPaymentInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $gateway;

    /**
     * AbstractCardPayment constructor.
     */
    public function __construct()
    {
        $this->gateway = env('CARD_PAYMENT_PARTNER');
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \T2G\Common\Contract\CardPaymentInterface|void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function logCallbackRequest(Request $request)
    {
        $this->logger->info("Card payment callback received", ['data' => $request->all(), 'gateway' => $this->gateway]);
    }

    /**
     * @inheritdoc
     */
    public function logCallbackProcessed($message)
    {
        $this->logger->info("Card payment callback processed", ['status' => $message, 'gateway' => $this->gateway]);
    }

    /**
     * @inheritdoc
     */
    public function logCardPaymentError(CardPaymentResponseInterface $response)
    {
        $this->logger->info("Card payment error", ['data' => $response->getBody(), 'gateway' => $this->gateway]);
    }
}
