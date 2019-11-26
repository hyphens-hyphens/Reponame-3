<?php

namespace T2G\Common\Models;

/**
 * Class UserLastLogin
 *
 * @package \T2G\Common\Models
 * @property int $id
 * @property int $user_id
 * @property string $last_login_date
 * @property string|null $last_logout_date
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel active()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|UserLastLogin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLastLogin whereLastLoginDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLastLogin whereLastLogoutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserLastLogin whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $hwid
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\UserLastLogin whereHwid($value)
 */
class UserLastLogin extends BaseEloquentModel
{
    protected $table = 'users_last_login';

    public $timestamps = false;
}
