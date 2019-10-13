<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Repository\UserRepository;
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

    public function index()
    {
        $data = [
            'widgetUser' => $this->getUserWidgetData(),
        ];

        return voyager()->view('voyager::index', $data);
    }

    /**
     * @return array
     */
    protected function getUserWidgetData()
    {
        $widget = app(UserWidget::class);

        return $widget->getData();
    }
}
