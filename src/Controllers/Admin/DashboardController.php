<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Controllers\Controller;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Widget\CCUWidget;
use T2G\Common\Widget\PaymentWidget;
use T2G\Common\Widget\UserWidget;
use T2G\Common\Widget\UserRankWidget;


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
     *
     * @param \T2G\Common\Repository\UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
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

        if (config('t2g_common.top_list_user_enable')) {
            $data['widgetUserRankList'] = $this->getWidgetUserRankList();
        }

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
    protected function getCCUWidget()
    {
        $widget = app(CCUWidget::class);

        return $widget->render();
    }

    /**
     * @return mixed
     */
    protected function getWidgetUserRankList()
    {
        $widget = app(UserRankWidget::class);

        return $widget->render();
    }
}
