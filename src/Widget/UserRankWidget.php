<?php
namespace T2G\Common\Widget;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use T2G\Common\Repository\UserRepository;

/**
 * Class UserRankWidget
 */
class UserRankWidget extends \T2G\Common\Widget\AbstractWidget
{
    const DEFAULT_RANKING_PAGE_SIZES = 10;
    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @var \T2G\Common\Services\Kibana\AbstractRankingService
     */
    protected $rankingService;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
        $serviceClass = config('t2g_common_widgets.ranking.service_class');
        $this->rankingService = app($serviceClass);
    }

    /**
     * @return \Illuminate\View\View
     */
    // view user rank
    public function loadWidget()
    {
        $serverInfo = config('t2g_common_widgets.ranking.servers');

        return view('t2g_common::voyager.dashboard.widgets.user-rank', [
            'serverInfo' => $serverInfo
        ]);
    }

    /**
     * @param $serverName
     * @return mixed
     */
    public function loadWidgetContent($serverName)
    {
        $data = [];
        $page = Paginator::resolveCurrentPage() ?: 1;
        if ($serverName) {
            $data = $this->getTopUserList($serverName, $page);
        }

        return view('t2g_common::voyager.partials.top_user_list', ['data' => $data]);
    }
    /**
     * @return array
     *
     */
    private function getTopUserList($server, $page = 1)
    {
        return $this->rankingService->getTopLevelList($server, self::DEFAULT_RANKING_PAGE_SIZES, $page);
    }

    /**
     * @return string
     */
    protected function getViewPermission()
    {
        return 'widget.user';
    }
}
