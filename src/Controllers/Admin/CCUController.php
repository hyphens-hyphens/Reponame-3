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
        $fromDate = request('fromDate', date('Y-m-d', strtotime("-2 weeks")));
        $toDate = request('toDate', date('Y-m-d'));
        $data = [
            'ccus'     => $JXApiClient->getCCUs(),
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
            'chart'    => $this->getCCUChartData($fromDate, $toDate),
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

    private function getCCUChartData($fromDate, $toDate)
    {
        $data = [];
        /** @var CCURepository $repository */
        $repository = app(CCURepository::class);
        $chartData = $repository->getCUUChartForReport($fromDate, $toDate);
        if (count($chartData)) {
            foreach ($chartData as $row) {
                $data['yAxisData'][$row->server][] = [intval($row->ccu)];
            }
        } else {
            $data['yAxisData']['N/A'] = [0];
        }


        return $data;
    }
}
