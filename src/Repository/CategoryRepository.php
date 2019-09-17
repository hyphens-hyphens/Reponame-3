<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\Category;

/**
 * Class PostRepository
 *
 * @package \T2G\Common\Repository
 */
class CategoryRepository extends AbstractEloquentRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Category::class;
    }

    /**
     * @param $slug
     *
     * @return \Illuminate\Database\Eloquent\Builder|Category|null
     */
    public function getCategoryBySlug($slug)
    {
        $query = $this->query();
        $query->where('slug', $slug)
            ->where('status', true)
        ;

        return $query->first();
    }

}
