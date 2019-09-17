<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * T2G\Common\Models\GiftCode
 *
 * @property int $id
 * @property string $code
 * @property int|null $is_used
 * @property string|null $expired_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property-read \T2G\Common\Models\AbstractUser|null $user
 * @method static Builder|GiftCode active()
 * @method static Builder|GiftCode notExpires()
 * @method static Builder|GiftCode notOwned()
 * @method static Builder|GiftCode orderByPublishDate()
 * @method static Builder|GiftCode unused()
 * @method static Builder|GiftCode whereCode($value)
 * @method static Builder|GiftCode whereCreatedAt($value)
 * @method static Builder|GiftCode whereExpiredDate($value)
 * @method static Builder|GiftCode whereId($value)
 * @method static Builder|GiftCode whereIsUsed($value)
 * @method static Builder|GiftCode whereUpdatedAt($value)
 * @method static Builder|GiftCode whereUserId($value)
 * @mixin \Eloquent
 */
class GiftCode extends BaseEloquentModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $userModelClass = config('t2g_common.models.user_model_class');

        return $this->belongsTo($userModelClass);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return mixed
     */
    public function scopeUnused(Builder $query)
    {
        return $query->where('is_used', false);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return mixed
     */
    public function scopeNotOwned(Builder $query)
    {
        return $query->where('user_id', NULL);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return mixed
     */
    public function scopeNotExpires(Builder $query)
    {
        $now = time();

        return $query->whereRaw("(expired_date < {$now} OR expired_date is NULL)");
    }
}
