<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * T2G\Common\Models\Server
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $game_server_id
 * @property string $status
 * @property string|null $display_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Server active()
 * @method static Builder|Server orderByPublishDate()
 * @method static Builder|Server whereCreatedAt($value)
 * @method static Builder|Server whereDisplayText($value)
 * @method static Builder|Server whereGameServerId($value)
 * @method static Builder|Server whereId($value)
 * @method static Builder|Server whereName($value)
 * @method static Builder|Server whereSlug($value)
 * @method static Builder|Server whereStatus($value)
 * @method static Builder|Server whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Server extends BaseEloquentModel
{
    const STATUS_OPEN = 'OPEN';
    const STATUS_CLOSE = 'CLOSE';
    const STATUS_DEV = 'DEV';

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * @param Server $server
     *
     * @return string
     */
    public static function slugPlayGame(Server $server)
    {
        return "S" . $server->game_server_id . "-". $server->slug;
    }
}
