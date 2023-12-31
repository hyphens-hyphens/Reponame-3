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
 * @property int $gift_code_id
 * @property int|null $issued_for
 * @property-read \T2G\Common\Models\GiftCode $giftCode
 * @property-read \App\User|null $issuedFor
 * @property-read \App\User|null $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\BaseEloquentModel active()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\BaseEloquentModel orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem unused()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereGiftCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereIssuedFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCodeItem whereUserId($value)
 */
class GiftCodeItem extends BaseEloquentModel
{
    protected $table = 'gift_code_items';

    protected $fillable = ['code', 'gift_code_id'];

    protected $dates = ['used_at', 'issued_at'];

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

    /**
     * @return bool
     */
    public function isUsed()
    {
        return boolval($this->user_id);
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool
     */
    public function issueForUser(AbstractUser $user)
    {
        $this->issued_for = $user->id;
        $this->issued_at = date('Y-m-d H:i:s');

        return $this->save();
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $typeGiftCode = $this->giftCode->type;
        $expire = config('t2g_common.gift_code.fancung.expired_days', '-10 days');
        if (
            ($this->issued_at != null)
            && $typeGiftCode === GiftCode::TYPE_FAN_CUNG
            && $this->issued_at->gettimestamp() < strtotime($expire)
        ) {
            return true;
        }
        return false;
    }
}
