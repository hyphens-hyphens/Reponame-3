<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Repository\UserRepository;
use T2G\Common\Widget\CCUWidget;
use T2G\Common\Widget\PaymentWidget;
use T2G\Common\Widget\UserWidget;
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

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $data = [
            'widgetUser'    => $this->getUserWidget(),
            'widgetPayment' => $this->getPaymentWidget(),
            'widgetCCU'     => $this->getCCUWidget(),
        ];

        return voyager()->view('voyager::index', $data);
    }

    /**
     * @return array
     */
    protected function getUserWidget()
    {
        $widget = app(UserWidget::class);

        return $widget->render();
    }

    /**
     * @return array
     */
    protected function getPaymentWidget()
    {
        $widget = app(PaymentWidget::class);

        return $widget->render();
    }

    /**
     * @return \Illuminate\View\View
     */
    private function getCCUWidget()
    {
        $widget = app(CCUWidget::class);

        return $widget->render();
    }
}
