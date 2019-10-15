<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\CCU;

/**
 * Class ServerRepository
 *
 * @package \App\Repository
 */
class CCURepository extends AbstractEloquentRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return CCU::class;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCCUChartForWidget()
    {
        $results = $this->db->table($this->model->getTable())
            ->selectRaw("
                `server`,
                ROUND(AVG(`online`)) as `ccu`, 
                DATE_FORMAT(`created_at`, '%y-%m-%d %H:00') AS `date`
            ")
            ->groupBy('server', 'date')
            ->orderBy('date', 'ASC')
            ->get()
        ;

        return $results;
    }

    public function getCUUChartForReport($fromDate, $toDate)
    {
        $results = $this->db->table($this->model->getTable())
            ->selectRaw("
                `server`,
                ROUND(AVG(`online`)) as `ccu`, 
                DATE_FORMAT(`created_at`, '%y-%m-%d %H:00') AS `date`
            ")
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('server', 'date')
            ->orderBy('date', 'ASC')
            ->get()
        ;

        return $results;
    }

}
