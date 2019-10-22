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
                online as `ccu`, 
                `created_at`
            ")
            ->where('created_at', '>=', date('Y-m-d', strtotime("-1 days")))
            ->orderBy('created_at', 'ASC')
            ->get()
        ;

        return $results;
    }

    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCCUChartForReport($fromDate, $toDate)
    {
        $results = $this->db->table($this->model->getTable())
            ->selectRaw("
                `server`,
                online as `ccu`,
                `created_at`
            ")
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'ASC')
            ->get()
        ;

        return $results;
    }

    public function getCCUForPeakReport($fromDate, $toDate)
    {
        $results = $this->query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('server', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->get()
        ;

        return $results;
    }

}
