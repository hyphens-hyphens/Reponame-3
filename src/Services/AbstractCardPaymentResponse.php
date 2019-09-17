<?php

namespace T2G\Common\Services;

use Illuminate\Log\LogManager;
use T2G\Common\Contract\CardPaymentResponseInterface;
use T2G\Common\Util\MobileCard;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractCardPaymentResponse
 *
 * @package \T2G\Common\Services
 */
abstract class AbstractCardPaymentResponse implements CardPaymentResponseInterface
{

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $transactionCode;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var LogManager
     */
    protected $logger;

    /**
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \T2G\Common\Util\MobileCard                $card
     */
    public function __construct(ResponseInterface $response, MobileCard $card)
    {
        /** @var LogManager logger */
        $this->logger = app(LogManager::class);
        $this->logger->channel('card_payment');
        $this->parseResponse($response, $card);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \T2G\Common\Util\MobileCard                $card
     */
    abstract protected function parseResponse(ResponseInterface $response, MobileCard $card);

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
