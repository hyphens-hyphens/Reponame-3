<?php

namespace T2G\Common\Repository;

use TCG\Voyager\Models\Page;

/**
 * Class PageRepository
 *
 * @package \App\Repository
 */
class PageRepository extends AbstractEloquentRepository
{

    /**
     * @return string
     */
    public function model(): string
    {
        return Page::class;
    }

    /**
     * @param $uri
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|\TCG\Voyager\Models\Page|null
     */
    public function getPageByUri($uri)
    {
        $query = $this->query();
        $query->where([
            'uri'    => $uri,
            'status' => true,
        ]);

        return $query->first();
    }
}
