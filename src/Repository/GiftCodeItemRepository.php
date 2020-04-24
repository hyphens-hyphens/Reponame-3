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
                    $query->where('name', 'LIKE', "%{$keyword}%");
                });
            }
            if ($field == 'code') {
                $query->where("`{$giftCodeItemTable}`.`code`", $keyword);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * @param string|null $code
     *
     * @return GiftCodeItem|null
     */
    public function getByCode(?string $code)
    {
        return $this->query()
            ->whereCode(strtoupper($code))
            ->first();
    }

    /**
     * @param \T2G\Common\Models\GiftCodeItem $giftCodeItem
     * @param \T2G\Common\Models\AbstractUser $user
     */
    public function updateUsedCode(GiftCodeItem $giftCodeItem, AbstractUser $user)
    {
        $giftCodeItem->user_id = $user->id;
        $giftCodeItem->save();
    }

    /**
     * check if user claimed same type gift code
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $giftCodeId
     *
     * @return bool
     */
    public function isUserClaimed(AbstractUser $user, $giftCodeId)
    {
        $query = $this->query();
        $query->where([
            'user_id'      => $user->id,
            'gift_code_id' => $giftCodeId,
        ]);

        return $query->count() > 0;
    }
}
