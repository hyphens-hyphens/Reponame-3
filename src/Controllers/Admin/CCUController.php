<?php

namespace T2G\Common\Controllers\Admin;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $chartData = $repository->getCUUChartForReport($fromDate, $toDate);
        if (count($chartData)) {
            $data['pointStart'] = strtotime($chartData->offsetGet(0)->created_at) * 1000;
            foreach ($chartData as $row) {
                $data['yAxisData'][$row->server][] = [intval($row->ccu)];
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
        $data = [];
        /** @var CCURepository $repository */
        $repository = app(CCURepository::class);
        $chartData = $repository->getPeakCUUChartForReport($fromDate, $toDate);
        if (count($chartData)) {
            foreach ($chartData as $row) {
                $data['xAxisData'][] = $row->date;
                $data['yAxisData']["{$row->server} Max CCU"][] = [intval($row->max_ccu)];
                $data['yAxisData']["{$row->server} Min CCU"][] = [intval($row->min_ccu)];
            }
            return $data;
        } else {
            $data['xAxisData'] = [];
            $data['yAxisData']['N/A'] = [0];
        }

        return $data;
    }
}
