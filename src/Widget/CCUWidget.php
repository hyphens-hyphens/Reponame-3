<?php

namespace T2G\Common\Widget;

use T2G\Common\Repository\CCURepository;
use T2G\Common\Services\JXApiClient;

/**
 * Class PaymentWidget
 *
 * @package \T2G\Common\Widget
 */
class CCUWidget extends AbstractWidget
{
    /**
     * @var \T2G\Common\Repository\CCURepository
     */
    protected $repository;

    /**
     * @var \T2G\Common\Services\JXApiClient
     */
    protected $jxApi;

    public function __construct(CCURepository $CCURepository, JXApiClient $jxApi)
    {
        $this->repository = $CCURepository;
        $this->jxApi = $jxApi;
    }

    /**
     * @return \Illuminate\View\View
     */
    protected function loadWidget()
    {
        $data = $this->getData();

        return view('t2g_common::voyager.dashboard.widgets.ccu', $data);
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $data = [
            'ccus'  => $this->jxApi->getCCUs(),
        ];
        $chartData = $this->repository->getCCUChartForWidget();
        // https://cdn.jsdelivr.net/gh/highcharts/highcharts@v7.0.0/samples/data/usdeur.json
        // prepare data for line time series chart
        foreach ($chartData as $k => $row) {
            if ($k == 0) {
                $data['chart']['pointStart'] = strtotime($row->date) * 1000; // milisecond timestamp
            }
            $data['chart']['yAxisData'][$row->server][] = [intval($row->ccu)];
        }
        if (!count($chartData)) {
            $data['chart']['yAxisData'] = ['N/A' => 0];
            $data['chart']['pointStart'] = 0;
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function getViewPermission()
    {
        return 'widget.ccu';
    }
}
