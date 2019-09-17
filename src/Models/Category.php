<?php

namespace T2G\Common\Models;

/**
 * T2G\Common\Models\Category
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int $order
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status
 * @property-read null $translated
 * @property-read \TCG\Voyager\Models\Category $parentId
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Post[] $posts
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @method static \Illuminate\Database\Eloquent\Builder|Category active()
 * @method static \Illuminate\Database\Eloquent\Builder|Category orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category withTranslation($locale = null, $fallback = true)
 * @method static \Illuminate\Database\Eloquent\Builder|Category withTranslations($locales = null, $fallback = true)
 * @mixin \Eloquent
 */
class Category extends \TCG\Voyager\Models\Category
{
    use BaseEloquentModelTrait;
}
