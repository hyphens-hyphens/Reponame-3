<?php

namespace T2G\Common\Exceptions;

use T2G\Common\Models\GiftCode;
use T2G\Common\Models\GiftCodeItem;

/**
 * Class GiftCodeException
 *
 * @package \T2G\Common\Exceptions
 */
class GiftCodeException extends \Exception
{
    const ERROR_CODE_DISABLE                    = 100;
    const ERROR_CODE_NOT_FOUND                  = 101;
    const ERROR_CODE_USED                       = 102;
    const ERROR_CODE_API_ERROR                  = 103;
    const ERROR_CODE_CLAIMED                    = 104;
    const ERROR_CODE_NOT_AVAILABLE              = 105;
    const ERROR_CODE_ISSUER_NOT_MATCH           = 106;
    const ERROR_ISSUE_CODE_RANGE_NOT_VALID      = 107;
    const ERROR_CODE_PER_MONTH_EXPIRED          = 108;
    const ERROR_CODE_WAS_USED_ONCE_IN_MONTH     = 109;

    /**
     * @var \T2G\Common\Models\GiftCodeItem
     */
    protected $giftCode;

    /**
     * @return \T2G\Common\Models\GiftCodeItem
     */
    public function getGiftCode()
    {
        return $this->giftCode;
    }

    /**
     * GiftCodeException constructor.
     *
     * @param                                      $code
     * @param \T2G\Common\Models\GiftCodeItem|null $giftCodeItem
     */
    public function __construct($code, GiftCodeItem $giftCodeItem = null)
    {
        $this->giftCode = $giftCodeItem;
        $messages       = [
            self::ERROR_CODE_DISABLE                    => "This gift code is disabled",
            self::ERROR_CODE_NOT_FOUND                  => "Gift code not found",
            self::ERROR_CODE_USED                       => "Gift code was used",
            self::ERROR_CODE_API_ERROR                  => "Cannot use code, API server return an error",
            self::ERROR_CODE_CLAIMED                    => "This user already claimed this type of gift code before",
            self::ERROR_CODE_NOT_AVAILABLE              => "Out of Gift Code for issuing",
            self::ERROR_CODE_ISSUER_NOT_MATCH           => "This gift code was issued for another user",
            self::ERROR_ISSUE_CODE_RANGE_NOT_VALID      => "The `from` and `to` arguments were not valid",
            self::ERROR_CODE_PER_MONTH_EXPIRED          => "The gift code was expired",
            self::ERROR_CODE_WAS_USED_ONCE_IN_MONTH     => "This type of code was used this month",

        ];

        parent::__construct($messages[$code] ?? "Unknown Error", $code);
    }

}
