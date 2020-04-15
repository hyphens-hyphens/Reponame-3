<?php

namespace T2G\Common\Widget;

use T2G\Common\Repository\CCURepository;

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
     * @var \T2G\Common\Services\GameApiClientInterface
     */
    protected $api;

    public function __construct(CCURepository $CCURepository)
    {
        $this->repository = $CCURepository;
        $this->api = getGameApiClient();
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    protected function getData()
    {
        $data = [];
        $chartData = $this->repository->getCCUChartForWidget();
        // https://cdn.jsdelivr.net/gh/highcharts/highcharts@v7.0.0/samples/data/usdeur.json
        // prepare data for line time series chart
        foreach ($chartData as $k => $row) {
            if ($k == 0) {
                $data['chart']['pointStart'] = strtotime($row->created_at) * 1000; // milisecond timestamp
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
