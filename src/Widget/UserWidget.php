<?php
namespace T2G\Common\Widget;

use T2G\Common\Repository\UserRepository;

/**
 * Class UserWidget
 */
class UserWidget extends AbstractWidget
{
    /**
     * @var \T2G\Common\Repository\UserRepository
     */
    protected $repository;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function loadWidget()
    {
        $data = $this->getData();

        return view('t2g_common::voyager.dashboard.widgets.user', $data);
    }

    /**
     * @return array
     *
     */
    protected function getData()
    {
        $data = [
            'todayDirectRegistered' => 0,
            'todayPaidRegistered'   => 0,
        ];

        $todayRegisteredData = $this->repository->getTodayRegisteredForWidget();
        foreach ($todayRegisteredData as $row) {
            if ($row->is_direct) {
                $data['todayDirectRegistered'] = $row->total;
            } else {
                $data['todayPaidRegistered'] = $row->total;
            }
        }

        $registeredChartData = $this->repository->getRegisteredChartForWidget();
        if (count($registeredChartData)) {
            foreach ($registeredChartData as $row) {
                $data['chart']['xAxisData'][] = $row->date;
                $data['chart']['yAxisData'][] = $row->total;
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
        return 'widget.user';
    }
}
