<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Controllers\Controller;
use T2G\Common\Repository\CCURepository;
use T2G\Common\Services\JXApiClient;

/**
 * Class CCUController
 *
 * @package \T2G\Common\Controllers\Admin
 */
class CCUController extends Controller
{
    public function report(JXApiClient $JXApiClient)
    {
        $fromDate = request('fromDate', date('Y-m-d 00:00:00', strtotime("-2 weeks")));
        $toDate = request('toDate', date('Y-m-d 23:59:59'));
        $data = [
            'ccus'     => $JXApiClient->getCCUs(),
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
            'timeSeriesChart'    => $this->getCCUTimeSeriesChartData($fromDate, $toDate),
            'peakChart' => $this->getCCUPeakChartData($fromDate, $toDate),
        ];

        return voyager()->view('t2g_common::voyager.ccus.report', $data);
    }

    /**
     * @param \T2G\Common\Services\JXApiClient $JXApiClient
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tick(JXApiClient $JXApiClient)
    {
        $ccus = $JXApiClient->getCCUs();

        return response()->json($ccus);
    }

    private function getCCUTimeSeriesChartData($fromDate, $toDate)
    {
        $data = [];
        /** @var CCURepository $repository */
        $repository = app(CCURepository::class);
        $chartData = $repository->getCCUChartForReport($fromDate, $toDate);
        if (count($chartData)) {
            $data['pointStart'] = strtotime($chartData->offsetGet(0)->created_at) * 1000;
            foreach ($chartData as $row) {
                $data['yAxisData'][$row->server][] = intval($row->ccu);
            }
            return $data;
        } else {
            $data['yAxisData']['N/A'] = [0];
            $data['pointStart'] = 0;
        }

        return $data;
    }

    private function getCCUPeakChartData($fromDate, $toDate)
    {
        $maintenanceTime = config('t2g_common.game_api.maintenance_time');
        $data = ['xAxisData' => [], 'yAxisData' => []];
        /** @var CCURepository $repository */
        $repository = app(CCURepository::class);
        $chartData = $repository->getCCUForPeakReport($fromDate, $toDate);
        if (!$chartData) {
            $data['yAxisData'] = ['N/A' => 0];

            return $data;
        }
        $maxCCUData = $minCCUData = [];
        foreach ($chartData as $row) {
            $date = $row->created_at->format('d-m');
            if (!isset($maxCCUData[$row->server][$date])) {
                $maxCCUData[$row->server][$date] = $row;
            } elseif ($row->online > $maxCCUData[$row->server][$date]->online) {
                $maxCCUData[$row->server][$date] = $row;
            }
            $time = intval($row->created_at->format('Hi'));
            // do not use CCU in maintenance time as min CCU
            if ($time > $maintenanceTime['start'] && $time < $maintenanceTime['end']) {
                continue;
            }
            if (!isset($minCCUData[$row->server][$date])) {
                $minCCUData[$row->server][$date] = $row;
            } elseif ($row->online < $minCCUData[$row->server][$date]->online) {
                $minCCUData[$row->server][$date] = $row;
            }
        }
        $chartData = [
            'Max CCU' => $maxCCUData,
            'Min CCU' => $minCCUData,
        ];
        foreach ($chartData as $label => $group) {
            foreach ($group as $server => $rows) {
                foreach ($rows as $date => $row) {
                    if (!in_array($date, $data['xAxisData'])) {
                        $data['xAxisData'][] = $date;
                    }
                    $data['yAxisData']["{$row->server} {$label}"][] = [
                        'value' => $row->online,
                        'x'     => array_search($date, $data['xAxisData']),
                        'time'  => $row->created_at->format('H:i'),
                    ];
                }
            }
        }
        ksort($data['yAxisData']);

        return $data;
    }
}
