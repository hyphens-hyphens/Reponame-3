<?php

namespace T2G\Common\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use T2G\Common\Models\AbstractUser;
use T2G\Common\Models\GiftCode;
use T2G\Common\Models\GiftCodeItem;

/**
 * Class UserGiftCodeRepository
 *
 * @package \T2G\Common\Repository
 */
class GiftCodeItemRepository extends AbstractEloquentRepository
{

    /**
     * @return string
     */
    public function model(): string
    {
        return GiftCodeItem::class;
    }

    /**
     * @param \T2G\Common\Models\GiftCode $giftCode
     * @param \Illuminate\Http\Request    $request
     * @param int                         $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCodeItems(GiftCode $giftCode, Request $request, $perPage = 50)
    {
        $keyword = $request->get('s');
        $field = $request->get('key');
        $giftCodeItemTable = $this->model->getTable();
        $query = $giftCode->details()
            ->selectRaw("(`{$giftCodeItemTable}`.`user_id` is not null) as `is_used`, `{$giftCodeItemTable}`.*")
            ->orderByRaw('`is_used` ASC, `updated_at` DESC')
        ;
        if ($keyword && $field) {
            if ($field == 'user') {
                $query->whereHas('owner', function (Builder $query) use ($keyword) {
                    $query->whereRaw('name LIKE ?', ["%{$keyword}%"]);
                });
            }
            if ($field == 'issued_for') {
                $query->whereHas('issuedFor', function (Builder $query) use ($keyword) {
                    $query->whereRaw('name LIKE ?', ["%{$keyword}%"]);
                });
            }
            if ($field == 'code') {
                $query->where("{$giftCodeItemTable}.code", $keyword);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * @param string|null $code
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|\T2G\Common\Models\GiftCodeItem|null
     */
    public function getByCode(?string $code)
    {
        return $this->query()
            ->where('code', strtoupper($code))
            ->first()
        ;
    }

    /**
     * @param \T2G\Common\Models\GiftCodeItem $giftCodeItem
     * @param \T2G\Common\Models\AbstractUser $user
     */
    public function updateUsedCode(GiftCodeItem $giftCodeItem, AbstractUser $user)
    {
        $giftCodeItem->user_id = $user->id;
        $giftCodeItem->used_at = date('Y-m-d H:i:s');
        $giftCodeItem->save();
    }

    /**
     * check if user claimed same type gift code
     *
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Models\GiftCodeItem $giftCodeItem
     *
     * @return bool
     */
    public function isUserClaimed(AbstractUser $user, GiftCodeItem $giftCodeItem)
    {
        $query = $this->query();
        $query->where([
            'user_id'      => $user->id,
            'gift_code_id' => $giftCodeItem->gift_code_id,
        ]);

        return $query->count() > 0;
    }

    /**
     * @param \T2G\Common\Models\GiftCode $giftCode
     *
     * @return GiftCodeItem|null
     */
    public function getAvailableCodeForIssuing(GiftCode $giftCode)
    {
        $query = $this->query();
        /** @var GiftCodeItem|null $code */
        $code = $query->where('gift_code_id', $giftCode->id)
            ->whereNull('issued_for')
            ->whereNull('user_id')
            ->first()
        ;

        return $code ?: null;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Models\GiftCode     $giftCode
     *
     * @return GiftCodeItem|null
     */
    public function getIssuedCodeForUser(AbstractUser $user, GiftCode $giftCode)
    : ?GiftCodeItem {
        $query = $this->query();
        $query->where('gift_code_id', $giftCode->id)
            ->where('issued_for', $user->id)
        ;
        /** @var GiftCodeItem $code */
        $code = $query->first();

        return $code;
    }

    /**
     * @param  \T2G\Common\Models\AbstractUser  $user
     * @param  \T2G\Common\Models\GiftCode      $giftCode
     *
     * @return int
     */
    public function getUnusedCodes(AbstractUser $user, GiftCode $giftCode)
    {
        $query = $this->query();
        $query->where('gift_code_id', $giftCode->id)
            ->where('issued_for', $user->id)
            ->where('user_id', null);

        if ($giftCode->type === $giftCode::TYPE_FAN_CUNG)
        {
            $expire     = config('t2g_common.giftcode.fancung.expired_days', '-10 days');
            $dateExpire = date('Y-m-d H:i:s', strtotime($expire));
            $query->whereDate('issued_at', '>' , $dateExpire);
        }

        return $query->count();
    }

    /**
     * @param \App\User|null $user
     * @param int            $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getGiftcodeHistories(?\App\User $user, $perPage = 20)
    {
        $query = $this->query();
        $query->where('issued_for', $user->id)
            ->orWhere('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
        ;

        return $query->paginate($perPage);
    }

    public function getCodeWasUsedInMonth(AbstractUser $user, GiftCodeItem $giftCodeItem)
    {
        $dateExpireOnceMonth = date('Y-m-1 00:00:00');
        $query = $this->query();
        $query->where('gift_code_id', $giftCodeItem->gift_code_id)
            ->where('user_id', $user->id)
            ->whereDate('used_at', '>' , $dateExpireOnceMonth);

        return $query->count();
    }

}
