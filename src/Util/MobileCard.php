<?php

namespace T2G\Common\Util;

/**
 * Class MobileCard
 *
 * @package \T2G\Common
 */
class MobileCard
{
    const TYPE_VIETTEL   = 'VIETTEL';
    const TYPE_MOBIFONE  = 'MOBIFONE';
    const TYPE_VINAPHONE = 'VINA';
    const TYPE_ZING      = 'ZING';
    const AMOUNTS = [
        10000,
        20000,
        30000,
        50000,
        100000,
        200000,
        300000,
        500000,
        1000000,
    ];

    /**
     * @var string
     */
    protected $serial;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $amount;

    public static function getCardList()
    {
        return [
            self::TYPE_VIETTEL => "Viettel",
            self::TYPE_MOBIFONE => "Mobifone",
            self::TYPE_VINAPHONE => "Vinaphone",
            self::TYPE_ZING => "Zing Card",
        ];
    }

    public function __toString()
    {
        return "[{$this->type} - {$this->amount}] Serial: {$this->serial}; PIN: {$this->code}";
    }

    public static function getAmountList()
    {
        return [
            50000,
            100000,
            200000,
            300000,
            500000,
            1000000,
        ];
    }

    /**
     * @return string
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * @param string $serial
     *
     * @return MobileCard
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return MobileCard
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return MobileCard
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return MobileCard
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
