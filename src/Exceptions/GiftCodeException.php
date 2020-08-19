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
    const ERROR_CODE_DISABLE   = 100;
    const ERROR_CODE_NOT_FOUND = 101;
    const ERROR_CODE_USED      = 102;
    const ERROR_CODE_API_ERROR = 103;
    const ERROR_CODE_CLAIMED   = 104;
    const ERROR_CODE_NOT_AVAILABLE   = 105;

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
            self::ERROR_CODE_DISABLE       => "This gift code is disabled",
            self::ERROR_CODE_NOT_FOUND     => "Gift code not found",
            self::ERROR_CODE_USED          => "Gift code was used",
            self::ERROR_CODE_API_ERROR     => "Cannot use code, API server return an error",
            self::ERROR_CODE_CLAIMED       => "This user already claimed this type of gift code before",
            self::ERROR_CODE_NOT_AVAILABLE => "Out of Gift Code for issuing",
        ];

        parent::__construct($messages[$code] ?? "Unknown Error", $code);
    }

}
