<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Repository\UserRepository;
use TCG\Voyager\Http\Controllers\Controller;

/**
 * Class DashboardController
 *
 * @package \T2G\Common\Http\Controllers\Admin
 */
class DashboardController extends Controller
{
    /** @var UserRepository */
    protected $userRepository;

    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        $this->userRepository = app(UserRepository::class);
    }

    public function index()
    {
        $fromDate = request('fromDate', date('Y-m-d', strtotime("-2 weeks")));
        $toDate = request('toDate', date('Y-m-d', strtotime('today')));
        $data = [
            'regToday'        => $this->userRepository->getTodayRegistered(),
            'regTotal'        => t2g_model('user')->count(),
            'fromDate'        => $fromDate,
            'toDate'          => $toDate,
            'registeredChart' => $this->getRegisteredChartData($fromDate, $toDate),
        ];

        return voyager()->view('voyager::index', $data);
    }

    protected function getRegisteredChartData($fromDate, $toDate)
    {
        list($reportData, $campaigns) = $this->userRepository->getUserRegisteredReport($fromDate, $toDate);
        // prepare chart data
        $registeredChartData = [
            'direct' => [],
            'mkt' => []
        ];
        $dateArray = [];
        foreach ($reportData as $date => $reportDatum) {
            $dateArray[] = $date;
            $direct = 0;
            $mkt = 0;
            foreach ($reportDatum['details'] as $cid => $total) {
                if ($cid == 'not-set|not-set|not-set') {
                    $direct += $total;
                } else {
                    $mkt += $total;
                }
            }
            $registeredChartData['direct'][] = $direct;
            $registeredChartData['mkt'][] = $mkt;
        }
        $data = [
            'dateArray'                  => $dateArray,
            'fromDate'                   => $fromDate,
            'toDate'                     => $toDate,
            'reportRegisteredByCampaign' => [
                'data'      => $reportData,
                'campaigns' => $campaigns,
            ],
            'registeredChartData'        => $registeredChartData,
        ];

        return $data;
    }
}
