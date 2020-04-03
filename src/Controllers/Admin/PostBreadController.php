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
        'title', 'slug', 'body', 'category_id', 'publish_date', 'created_at', 'group_name'
    ];

    /**
     * @return array
     */
    protected function getEditableFields()
    {
        // Subclass should return list editable fields to perform quick edit action
        return ['group_name', 'group_title', 'group_order', 'group_sub'];
    }

    protected function alterBreadBrowseEloquentQuery(Builder $query, Request $request)
    {
        $query->with('category');
    }
}
