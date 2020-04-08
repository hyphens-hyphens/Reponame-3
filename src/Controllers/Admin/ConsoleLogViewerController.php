<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Controllers\Controller;
use T2G\Common\Services\Kibana\AccountService;

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

    public function viewLogHWID(AccountService $accountService)
    {
        $users = request('u');
        $days = intval(request('d', 10));
        $users = explode(',', $users);
        $results = $accountService->getHwidLogsByUsernames($users, 100, $days);

        return view('t2g_common::console.hwid', ['results' => $results]);
    }
}
