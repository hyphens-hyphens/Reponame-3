<?php

namespace T2G\Common\Repository;


use T2G\Common\Models\AbstractUser;
use T2G\Common\Models\GiftCode;

/**
 * Class GiftCodeRepository
 *
 * @package \T2G\Common\Repository
 */
class GiftCodeRepository extends AbstractEloquentRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return GiftCode::class;
    }

    /**
     * @param AbstractUser $user
     *
     * @return GiftCode|null
     */
    public function getCodeOwnedByUser(AbstractUser $user)
    {
        $query = $this->query();
        $query->where('user_id', $user->id);
        /** @var GiftCode|null $code */
        $code = $query->first();

        return $code;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|\T2G\Common\Models\GiftCode|null
     */
    public function getWelcomeCode()
    {
        $query = $this->query();
        $query->unused()
            ->notExpires()
            ->notOwned()
        ;

        return $query->first();
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Models\GiftCode     $giftCode
     */
    public function issueCodeForUser(AbstractUser $user, GiftCode $giftCode)
    {
        $giftCode->user_id = $user->id;
        $giftCode->save();
    }
}
