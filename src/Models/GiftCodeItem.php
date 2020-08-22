<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * T2G\Common\Models\UserGiftCode
 *
 * @property int $id
 * @property string $code
 * @property int|null $is_used
 * @property string|null $expired_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @mixin \Eloquent
 */
class GiftCodeItem extends BaseEloquentModel
{
    protected $table = 'gift_code_items';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function giftCode()
    {
        return $this->belongsTo(GiftCode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $userModelClass = config('t2g_common.models.user_model_class');

        return $this->belongsTo($userModelClass, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuedFor()
    {
        $userModelClass = config('t2g_common.models.user_model_class');

        return $this->belongsTo($userModelClass, 'issued_for');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return mixed
     */
    public function scopeUnused(Builder $query)
    {
        return $query->whereNull('user_id');
    }

    public function isUsed()
    {
        return boolval($this->user_id);
    }
}
