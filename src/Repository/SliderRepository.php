<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\Slider;

/**
 * Class \App\Models\SliderRepository
 *
 * @package \App\Repository
 */
class SliderRepository extends AbstractEloquentRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Slider::class;
    }

    /**
     * @param int $limit
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]
     */
    public function getHomeSlider($limit = self::DEFAULT_PER_PAGE)
    {
        $query = $this->query();
        $query->active()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
        ;

        return $query->get();
    }
}
