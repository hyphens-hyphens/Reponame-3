<?php

namespace T2G\Common\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Class PostBreadController
 *
 * @package \App\Http\Controllers\Admin
 */
class PostBreadController extends BaseVoyagerController
{
    protected $searchable = [
        'title', 'slug', 'body', 'category_id', 'publish_date', 'created_at', 'group', 'group_title', 'group_order'
    ];

    protected function alterBreadBrowseEloquentQuery(Builder $query, Request $request)
    {
        $query->with('category');
    }
}
