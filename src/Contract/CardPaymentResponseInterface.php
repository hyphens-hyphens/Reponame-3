<?php

namespace T2G\Common\Contract;

/**
 * Class CardPaymentResponseInterface
 */
interface CardPaymentResponseInterface
{
    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return bool
     */
    public function isSuccess();

    /**
     * @return string
     */
    public function getTransactionCode();

    /**
     * @return array
     */
    public function getErrors();

}
