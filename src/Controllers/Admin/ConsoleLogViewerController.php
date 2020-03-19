<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Controllers\Controller;

/**
 * Class ConsoleViewerController
 *
 * @package \T2G\Common\Controllers\Admin
 */
class ConsoleLogViewerController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function viewLogKimYen()
    {
        $timestamp = request('t');
        $dataFile = storage_path('app/console_log/kimyen_' . $timestamp . '.html');

        return response(file_get_contents($dataFile));
    }
}
