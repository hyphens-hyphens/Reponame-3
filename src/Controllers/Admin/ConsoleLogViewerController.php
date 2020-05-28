<?php

namespace T2G\Common\Controllers\Admin;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use T2G\Common\Controllers\Controller;
use T2G\Common\Services\Kibana\AbstractKibanaService;
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
        $token = request('t');
        $dataFile = storage_path('app/console_log/kimyen_' . $token);
        if (file_exists($dataFile . ".html")) {
            // old data file with complete view rendered
            return response(file_get_contents($dataFile . ".html"));
        }

        try {
            $data = json_decode(file_get_contents($dataFile), 1);
        } catch (\Exception $e) {
            throw new NotFoundHttpException();
        }

        return view('t2g_common::console.kimyen_keoxe', $data);
    }

    public function viewLogHWID(AccountService $accountService)
    {
        $users = request('u');
        $server = request('s');
        $days = intval(request('d', 10));
        $users = explode(',', $users);
        $results = $accountService->getHwidLogsByUsernames($users, AbstractKibanaService::MAX_RESULTS_WINDOW_INNER, $days, $server);

        return view('t2g_common::console.log_hwid', ['results' => $results]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function viewLogMultiLogin()
    {
        $timestamp = request('t');
        $dataFile = storage_path('app/console_log/multi_login_' . $timestamp);
        if (file_exists($dataFile . ".html")) {
            // old data file with complete view rendered
            return response(file_get_contents($dataFile . ".html"));
        }

        try {
            $data = json_decode(file_get_contents($dataFile), 1);
        } catch (\Exception $e) {
            throw new NotFoundHttpException();
        }
        $version = $data['version'] ?? '';

        return view('t2g_common::console.multi_login' . $version, $data);
    }
}
