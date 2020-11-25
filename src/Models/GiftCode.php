<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;

/**
 * T2G\Common\Models\GiftCode
 *
 * @property int                                       $id
 * @property string                                    $code
 * @property int|null                                  $is_used
 * @property string|null                               $expired_date
 * @property \Illuminate\Support\Carbon|null           $created_at
 * @property \Illuminate\Support\Carbon|null           $updated_at
 * @property int|null                                  $user_id
 * @property-read \T2G\Common\Models\AbstractUser|null $user
 * @property string                                     prefix
 * @property string                                     type
 * @property string                                     code_name
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
 * @property int $status
 * @property string|null $name
 * @property int $is_claimable
 * @property-read \Illuminate\Database\Eloquent\Collection|\T2G\Common\Models\GiftCodeItem[] $details
 * @property-read int|null $details_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode whereCodeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode whereIsClaimable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\GiftCode whereType($value)
 */
class GiftCode extends BaseEloquentModel
{
    use Notifiable;

    const TYPE_PER_ACCOUNT   = 'per-account';
    const TYPE_PER_SERVER    = 'per-server';
    const TYPE_PER_CHARACTER = 'per-character';

    /** @var array  */
    protected $fillable = ['name', 'prefix', 'type', 'expired_at', 'code_name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(GiftCodeItem::class, 'gift_code_id');
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

    /**
     *
     * @return int
     */
    public function getNumberOfUsedCodes()
    {
        return $this->details()->whereNotNull('user_id')->count();
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool
     */
    public function isUserClaimed(AbstractUser $user)
    {
        $query = $this
            ->where('id', $this->id)
            ->whereHas('details', function (Builder $query) use ($user){
                $query->where('issued_for', $user->id);
            })
        ;

        return $query->count() > 0;
    }
}
