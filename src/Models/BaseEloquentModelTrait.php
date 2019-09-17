<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class BaseEloquentModelTrait
 */
trait BaseEloquentModelTrait
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 1);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByPublishDate(Builder $query)
    {
        return $query->orderBy('publish_date', 'DESC');
    }
}
