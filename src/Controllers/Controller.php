<?php

namespace T2G\Common\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * Class Controller
 *
 * @package \T2G\Common\Controllers
 */
class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct() {
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.default');
    }
}
