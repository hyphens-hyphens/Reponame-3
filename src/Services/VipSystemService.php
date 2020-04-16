<?php

namespace T2G\Common\Services;

use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Util\CommonHelper;

/**
 * Class VipSystemService
 *
 * @package \T2G\Common\Services
 */
class VipSystemService
{
    protected static $cacheVipLevel = [];
    protected static $cacheTotalPaid = [];

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return mixed
     */
    public static function getTotalVipPaidOfUser(\T2G\Common\Models\AbstractUser $user)
    {
        $phone = CommonHelper::cleanPhoneValue($user->phone);
        if (isset(self::$cacheTotalPaid[$phone])) {
            return self::$cacheTotalPaid[$phone];
        }
        $paymentRepository = app(PaymentRepository::class);
        $vipTotalPaid = $paymentRepository->getTotalPaidForVipSystem($user);
        $bonusAccs = config('t2g_common.vip_system.bonus_accs');
        $bonus = $bonusAccs[$phone] ?? 0;
        $vipTotalPaid += $bonus;
        self::$cacheTotalPaid[$phone] = $vipTotalPaid;

        return $vipTotalPaid;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return int|mixed|string
     */
    public static function getVipLevel(\T2G\Common\Models\AbstractUser $user)
    {
        if (isset(self::$cacheVipLevel[$user->id])) {
            return self::$cacheVipLevel[$user->id];
        }
        $totalPaid = self::getTotalVipPaidOfUser($user);
        $vipLevels = config('t2g_common.vip_system.levels');
        $vip = 0;
        foreach ($vipLevels as $level => $amount) {
            if ($totalPaid < $amount) {
                $vip = $level;
                break;
            }
        }
        self::$cacheVipLevel[$user->id] = $vip;

        return $vip;
    }
}
