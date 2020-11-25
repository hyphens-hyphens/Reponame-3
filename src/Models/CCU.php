<?php

namespace T2G\Common\Models;

/**
 * T2G\Common\Models\CCU
 *
 * @property int $id
 * @property string $server
 * @property int|null $online
 * @property \Illuminate\Support\Carbon|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel active()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|CCU whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CCU whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CCU whereOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CCU whereServer($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\CCU newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\CCU newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\CCU query()
 */
class CCU extends BaseEloquentModel
{
    const UPDATED_AT = null;

    protected $table = 'ccus';
}
