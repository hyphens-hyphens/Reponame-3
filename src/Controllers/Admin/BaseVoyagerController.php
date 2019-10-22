<?php

namespace T2G\Common\Controllers\Admin;

use TCG\Voyager\Http\Controllers\VoyagerBaseController;
/**
 * Class BaseVoyagerController
 *
 * @package \T2G\Common\Controllers\Admin
 */
class BaseVoyagerController extends VoyagerBaseController
{
    public function __construct() {
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.default');
    }
}
