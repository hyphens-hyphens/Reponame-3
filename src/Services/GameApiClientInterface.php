<?php

namespace T2G\Common\Services;

use T2G\Common\Models\GiftCode;

interface GameApiClientInterface
{
    /**
     * @param $username
     * @param $password
     *
     * @return bool
     */
    public function createUser($username, $password);

    /**
     * @param $username
     * @param $newPassword
     *
     * @return bool
     */
    public function setPassword($username, $newPassword);

    /**
     * @param $username
     * @param $newSecondaryPassword
     *
     * @return bool
     */
    public function setSecondaryPassword($username, $newSecondaryPassword);

    /**
     * @param      $username
     * @param int  $knb
     * @param int  $xu
     * @param null $orderId
     *
     * @return bool
     */
    public function addGold($username, $knb = 0, $xu = 0, $orderId = null);

    /**
     * @param                             $username
     * @param \T2G\Common\Models\GiftCode $giftCode
     * @param bool                        $forceUpdate
     *
     * @return bool
     */
    public function addGiftCode($username, GiftCode $giftCode, $forceUpdate = false);

    /**
     * @return array
     */
    public function getCCUs();

    /**
     * @return string|null
     */
    public function getLastResponse();
}
