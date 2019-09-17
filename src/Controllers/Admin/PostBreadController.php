<?php

namespace T2G\Common\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

/**
 * Class PostBreadController
 *
 * @package \App\Http\Controllers\Admin
 */
class PostBreadController extends VoyagerBaseController
{
    protected $searchable = [
        'title', 'slug', 'body', 'category_id', 'publish_date', 'created_at'
    ];

    protected function alterBreadBrowseEloquentQuery(Builder $query, Request $request)
    {
        $query->with('category');
    }
}
