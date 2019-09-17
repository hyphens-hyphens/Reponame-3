<?php

namespace T2G\Common\Models;

use TCG\Voyager\Traits\Resizable;

/**
 * T2G\Common\Models\Banner
 *
 * @property int $id
 * @property string $title
 * @property string $link
 * @property string|null $image
 * @property int $status
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Banner active()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Banner extends BaseEloquentModel
{
    use Resizable;
}
