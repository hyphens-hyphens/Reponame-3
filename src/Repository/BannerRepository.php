<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\Banner;

/**
 * Class BannerRepository
 *
 * @package \App\Repository
 */
class BannerRepository extends AbstractEloquentRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model():string
    {
        return Banner::class;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getActiveBanner()
    {
        /** @var Banner|\Illuminate\Database\Eloquent\Builder $query */
        $query = $this->query();
        $query->active()
            ->orderBy('id', 'desc')
        ;
        /** @var Banner|null $banner */
        $banner = $query->first();

        return $banner;
    }
}
