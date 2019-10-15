<?php

namespace T2G\Common\Widget;

use T2G\Common\Repository\PaymentRepository;

/**
 * Class PaymentWidget
 *
 * @package \T2G\Common\Widget
 */
class PaymentWidget extends AbstractWidget
{
    /**
     * @var \T2G\Common\Repository\PaymentRepository
     */
    protected $repository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->repository = $paymentRepository;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function loadWidget()
    {
        $data = $this->getData();

        return view('t2g_common::voyager.dashboard.widgets.payment', $data);
    }

    protected function getData()
    {
        $data = [
            'todayRevenue'     => $this->repository->getRevenueByPeriod(date('Y-m-d')),
            'thisMonthRevenue' => $this->repository->getRevenueByPeriod(date('Y-m-01')),
        ];

        $revenueData = $this->repository->getRevenueChartForWidget();
        if (count($revenueData)) {
            foreach ($revenueData as $row) {
                $data['chart']['xAxisData'][] = $row->date;
                $data['chart']['yAxisData'][] = intval($row->total);
            }
        } else {
            // fallback chart data
            $data['chart'] = [
                'xAxisData' => [date('d-m')],
                'yAxisData' => [0],
            ];
        }


        return $data;
    }

    /**
     * @return string
     */
    protected function getViewPermission()
    {
        return 'widget.payment';
    }

}
