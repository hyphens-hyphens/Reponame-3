<?php
namespace T2G\Common\Controllers\Admin;

use T2G\Common\Widget\UserRankWidget;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class RankingController extends  VoyagerBaseController
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    public function getTopUserList(\Illuminate\Http\Request $request)
    {
        $data   = $request->all();
        $widget = app(UserRankWidget::class);

        return $widget->loadWidgetContent($data['serverName']);
    }

}
