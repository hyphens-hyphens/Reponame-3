<?php

namespace T2G\Common\Models;

use TCG\Voyager\Traits\Resizable;

/**
 * T2G\Common\Models\Slider
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
 * @method static \Illuminate\Database\Eloquent\Builder|Slider active()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slider whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Slider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Slider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Slider query()
 */
class Slider extends BaseEloquentModel
{
    use Resizable;

    /**
     * @return string|null
     */
    public function displayLink()
    {
        $domains = config('t2g_common.site.domains');
        $siteDomain = $_SERVER['HTTP_HOST'] ?? null;
        if (empty($siteDomain)) {
            return $this->link;
        }
        $urlParsed = parse_url($this->link);
        if (!empty($urlParsed['host']) && in_array($urlParsed['host'], $domains) && $urlParsed['host'] != $siteDomain) {
            return str_replace($urlParsed['host'], $siteDomain, $this->link);
        }

        return $this->link;
    }
}
