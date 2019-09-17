<?php

namespace T2G\Common\Models;

use TCG\Voyager\Traits\Resizable;

/**
 * T2G\Common\Models\Gallery
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $images
 * @property int|null $order
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery active()
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gallery whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Gallery extends BaseEloquentModel
{
    use Resizable;
}
