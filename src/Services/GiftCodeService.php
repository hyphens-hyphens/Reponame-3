<?php

namespace T2G\Common\Services;

use Illuminate\Support\Str;
use T2G\Common\Exceptions\GiftCodeException;
use T2G\Common\Models\AbstractUser;
use T2G\Common\Models\GiftCode;
use T2G\Common\Models\GiftCodeItem;
use T2G\Common\Repository\GiftCodeItemRepository;
use T2G\Common\Repository\GiftCodeRepository;

/**
 * Class GiftCodeService
 *
 * @package \T2G\Common\Services
 */
class GiftCodeService
{
    /**
     * @var \T2G\Common\Repository\GiftCodeRepository
     */
    protected $giftCodeRepo;

    /**
     * @var \T2G\Common\Repository\GiftCodeItemRepository
     */
    protected $giftCodeItemRepo;

    /**
     * @var \T2G\Common\Services\GameApiClientInterface
     */
    protected $gameApi;

    /**
     * @var \Illuminate\Redis\Connections\PredisConnection|mixed
     */
    protected $redis;

    /**
     * GiftCodeService constructor.
     *
     * @param \T2G\Common\Repository\GiftCodeRepository     $giftCodeRepo
     * @param \T2G\Common\Repository\GiftCodeItemRepository $giftCodeItemRepo
     * @param \T2G\Common\Services\GameApiClientInterface   $gameApi
     */
    public function __construct(GiftCodeRepository $giftCodeRepo, GiftCodeItemRepository $giftCodeItemRepo, GameApiClientInterface $gameApi)
    {
        $this->giftCodeRepo = $giftCodeRepo;
        $this->giftCodeItemRepo = $giftCodeItemRepo;
        $this->gameApi = $gameApi;
        $this->redis = app('redis.connection');;
    }

    /**
     * @param \T2G\Common\Models\GiftCode $giftCode
     * @param                             $quantity
     * @param int                         $suffixLength
     *
     * @return bool
     */
    public function generateCode(GiftCode $giftCode, $quantity, $suffixLength = 6)
    {
        /** @var \Illuminate\Support\Collection $existedCode */
        $existedCode = $giftCode->details;
        if ($existedCode) {
            $existedCode = array_column($existedCode->toArray(), 'code');
        }
        $codes = [];
        $created = 0;
        while ($created < $quantity) {
            $codeValue = strtoupper($giftCode->prefix . Str::random($suffixLength));
            if (in_array($codeValue, $existedCode)) {
                continue;
            }
            $code = [
                'code'         => $codeValue,
                'gift_code_id' => $giftCode->id,
            ];
            $codes[] = $code;
            $existedCode[] = $code;
            $created++;
        }

        return GiftCodeItem::insert($codes);
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Models\GiftCodeItem $giftCodeItem
     *
     * @return bool
     * @throws \T2G\Common\Exceptions\GiftCodeException
     */
    public function useCode(AbstractUser $user, GiftCodeItem $giftCodeItem)
    {
//        if ($this->checkRaceCondition($giftCodeItem->code)) {
//            return false;
//        }
        if (!empty($giftCodeItem->issued_for) && $user->id != $giftCodeItem->issued_for) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_ISSUER_NOT_MATCH, $giftCodeItem);
        }

        if ($claimed = $this->giftCodeItemRepo->isUserClaimed($user, $giftCodeItem->gift_code_id)) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_CLAIMED, $giftCodeItem);
        }

        $giftCode = $giftCodeItem->giftCode;
        if ($giftCodeItem->isUsed()) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_USED, $giftCodeItem);
        }
        if (!$giftCode->status) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_DISABLE, $giftCodeItem);
        }

        // add gift code
        if (!$this->gameApi->addGiftCode($user->name, $giftCode)) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_API_ERROR, $giftCodeItem);
        }
        $this->giftCodeItemRepo->updateUsedCode($giftCodeItem, $user);

        return true;
    }

//    /**
//     * @param string|null $key
//     *
//     * @return bool
//     */
//    private function checkRaceCondition(?string $key)
//    {
//        $value = $this->redis->incr($key);
//        $this->redis->expireat($key, time() + 3);
//
//        return $value > 1;
//    }

    /**
     * @param string|null $code
     *
     * @return \T2G\Common\Models\GiftCodeItem|null
     * @throws \T2G\Common\Exceptions\GiftCodeException
     */
    public function getGiftCodeItem(?string $code)
    {
        $giftCodeItem = $this->giftCodeItemRepo->getByCode($code);
        if (!$giftCodeItem || !$giftCodeItem->giftCode) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_NOT_FOUND);
        }

        return $giftCodeItem;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Models\GiftCode     $giftCode
     *
     * @return GiftCodeItem|null
     * @throws \T2G\Common\Exceptions\GiftCodeException
     */
    public function issueCodeToUser(AbstractUser $user, GiftCode $giftCode)
    {
        $code = $this->giftCodeItemRepo->getAvailableCodeForIssuing($giftCode);
        if (!$code) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_NOT_AVAILABLE);
        }
        $code->issued_for = $user->id;
        $code->save();

        return $code;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getRegisteredGiftCode(AbstractUser $user)
    {
        if (empty(config('site.gift_code.gift_code_issuing_id'))) {
            return null;
        }
        $giftCode = GiftCode::find(config('site.gift_code.gift_code_issuing_id'));

        return $this->giftCodeItemRepo->getIssuedCodeForUser($user, $giftCode);
    }
}
