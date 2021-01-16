<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Models\Revision;
use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Repository\UserRepository;
use Illuminate\Http\Request;
use T2G\Common\Widget\UserWidget;

/**
 * Class WidgetController
 * @package T2G\Common\Controllers\Admin
 */

class WidgetController extends BaseVoyagerController
{
    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshData(\Illuminate\Http\Request $request)
    {
        $data          = $request->all();
        $widgetService = '';
        switch ($data['classService']){
            case 'UserWidget':
                $widgetService = app(\T2G\Common\Widget\UserWidget::class);
        }
        $data          =  $widgetService->getData();

        return response()->json($data);
    }
}
