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
            ->where('created_at', '>=', date('Y-m-d', strtotime("-3 days")))
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
    public function getCUUChartForReport($fromDate, $toDate)
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

    public function getPeakCUUChartForReport($fromDate, $toDate)
    {
        $table = $this->model->getTable();
        $results = $this->db->table($table)
            ->selectRaw("
                `server`,
                MAX(online) as `max_ccu`,
                MIN(online) as `min_ccu`,
                DATE_FORMAT(`created_at`, '%d-%m') AS `date`,
                DATE_FORMAT(`created_at`, '%y-%m-%d') AS `ordered_date`
            ")
            ->whereBetween('created_at', [$fromDate, $toDate])
            // do not count CCU on maintenance time
            ->whereRaw("
                `created_at` BETWEEN '{$fromDate}' AND '{$toDate}'
                AND `id` NOT IN (SELECT id from {$table} WHERE CAST(DATE_FORMAT(`created_at`, '%k%i') AS UNSIGNED) BETWEEN 1620 AND 1710)
            ")
            ->groupBy('server', 'date', 'ordered_date')
            ->orderBy('server', 'ASC')
            ->orderBy('ordered_date', 'ASC')
            ->get()
        ;

        return $results;
    }

}
