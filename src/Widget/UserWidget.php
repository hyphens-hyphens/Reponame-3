<?php
namespace T2G\Common\Widget;

use T2G\Common\Repository\UserRepository;

/**
 * Class UserWidget
 *
 * @package \\${NAMESPACE}
 */
class UserWidget
{
    protected $repository;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    /**
     * @return array
     *
     */
    public function getData()
    {
        $data = ['todayDirectRegistered' => 0, 'todayPaidRegistered' => 0, 'registeredChart' => []];
        $todayRegisteredData = $this->repository->getTodayRegisteredForWidget();
        foreach ($todayRegisteredData as $row) {
            if ($row->is_direct) {
                $data['todayDirectRegistered'] = $row->total;
            } else {
                $data['todayPaidRegistered'] = $row->total;
            }
        }

        $data['chart'] = $this->repository->getRegisteredChartForWidget();

        return $data;
    }
}
