<?php

namespace T2G\Common\Services;

use Illuminate\Support\Str;
use T2G\Common\Exceptions\GiftCodeException;
use T2G\Common\Models\AbstractUser;
use T2G\Common\Models\GiftCode;
use T2G\Common\Models\GiftCodeItem;
use T2G\Common\Repository\GiftCodeItemRepository;
use T2G\Common\Repository\GiftCodeRepository;
use T2G\Common\Repository\UserRepository;

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
     * @var \T2G\Common\Repository\UserRepository
     */
    protected $userRepo;

    /**
     * GiftCodeService constructor.
     *
     * @param \T2G\Common\Repository\GiftCodeRepository     $giftCodeRepo
     * @param \T2G\Common\Repository\GiftCodeItemRepository $giftCodeItemRepo
     * @param \T2G\Common\Repository\UserRepository         $userRepository
     * @param \T2G\Common\Services\GameApiClientInterface   $gameApi
     */
    public function __construct(
        GiftCodeRepository $giftCodeRepo,
        GiftCodeItemRepository $giftCodeItemRepo,
        UserRepository $userRepository,
        GameApiClientInterface $gameApi
    ) {
        $this->giftCodeRepo = $giftCodeRepo;
        $this->giftCodeItemRepo = $giftCodeItemRepo;
        $this->userRepo = $userRepository;
        $this->gameApi = $gameApi;
        $this->redis = app('redis.connection');
    }

    /**
     * @param \T2G\Common\Models\GiftCode $giftCode
     * @param                             $quantity
     * @param int                         $suffixLength
     *
     * @return array
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
            $giftCodeItem = $this->giftCodeItemRepo->create($code);
            $codes[] = $giftCodeItem;
            $existedCode[] = $codeValue;
            $created++;
        }

        return $codes;
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
        if ($giftCodeItem->isUsed()) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_USED, $giftCodeItem);
        }

        if (!empty($giftCodeItem->issued_for) && $user->id != $giftCodeItem->issued_for) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_ISSUER_NOT_MATCH, $giftCodeItem);
        }
        $giftCode = $giftCodeItem->giftCode;
        $expire = config('t2g_common.giftcode.type_fancung.expried_days', '-10 days');
        if ($giftCode->type === GiftCode::TYPE_FAN_CUNG) {
            if ($giftCodeItem->issued_at && $giftCodeItem->issued_at->getTimestamp() < strtotime($expire)) {
                throw new GiftCodeException(GiftCodeException::ERROR_CODE_PER_MONTH_EXPIRED, $giftCodeItem);
            }
        } elseif ($claimed = $this->giftCodeItemRepo->isUserClaimed($user, $giftCodeItem)) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_CLAIMED, $giftCodeItem);
        }


        if (!$giftCode->status) {
            throw new GiftCodeException(GiftCodeException::ERROR_CODE_DISABLE, $giftCodeItem);
        }

        // add gift code
        $forceUpdate = $giftCode->type == GiftCode::TYPE_FAN_CUNG;
        if (!$this->gameApi->addGiftCode($user->name, $giftCode, $forceUpdate)) {
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

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Models\GiftCode     $giftCode
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function claimCode(AbstractUser $user, GiftCode $giftCode)
    {
        $codes = $this->generateCode($giftCode, 1);
        $code = $codes[0];
        /** @var GiftCodeItem $code */
        $code->fresh();
        $code->issued_for = $user->id;
        $this->issued_at = date('Y-m-d H:i:s');
        $code->save();

        return $code;
    }

    /**
     * @param \T2G\Common\Models\GiftCode $giftCode
     * @param string                      $username
     * @param int                         $from
     * @param int                         $to
     *
     * @return array $messages
     */
    public function addCodeForUsers(GiftCode $giftCode, string $username, int $from = 0, int $to = 1)
    {
        $messages = [];
        if ($from === $to) {
            $from = 0;
            $to = 1;
        }
        if ($from >= $to || $from < 0 || $to < 0 || ($to - $from > 20)) {
            $messages[] = "Số bắt đầu và kết thúc không hợp lệ";
            return $messages;
        }
        for ($i = $from; $i < $to; $i++) {
            $name = ($to - $from) > 1 ? $username . $i : $username;
            $user = $this->userRepo->getUserByName($name);
            if (!$user) {
                $messages[] = "Tài khoản `{$name}` không tồn tại";
                continue;
            }
            $message = $this->_addCodeForUser($giftCode, $user);
            if ($message === true) {
                // success
                $messages[] = "Add code thành công cho tài khoản `{$name}`";
            } else {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    /**
     * @param \T2G\Common\Models\GiftCode     $giftCode
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool|string
     */
    private function _addCodeForUser(GiftCode $giftCode, AbstractUser $user)
    {
        $unusedCodes = $this->giftCodeItemRepo->getUnusedCodes($user, $giftCode);
        if ($giftCode->type !== GiftCode::TYPE_FAN_CUNG && $unusedCodes > 0) {
            return "Tài khoản `{$user->name}` đã được add code này rồi";
        }
        if ($giftCode->type == GiftCode::TYPE_FAN_CUNG && $unusedCodes >= 2) {
            return "Tài khoản `{$user->name}` còn 2 code chưa sử dụng";
        }
        // check code is_claimable or not
        if ($giftCode->is_claimable) {
            $this->claimCode($user, $giftCode);

            return true;
        }
        $codeItem = $this->giftCodeItemRepo->getAvailableCodeForIssuing($giftCode);
        if (!$codeItem) {
            return "Không còn gift code để phát cho tài khoản `{$user->name}`";
        }
        $codeItem->issueForUser($user);

        return true;
    }
}
