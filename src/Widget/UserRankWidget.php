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
    /**
     * @var UserRepository
     */
    protected $repository;
    const PAGE_SIZE_HOME_RANKING = 10;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    /**
     * @return \Illuminate\View\View
     */
    // view user rank
    public function loadWidget()
    {
        $serverInfo = config('t2g_common.server_info');

        return view('t2g_common::voyager.dashboard.widgets.user-rank',[
            'serverInfo' => $serverInfo
        ]);
    }

    /**
     * @param $serverName
     * @return mixed
     */
    public function loadWidgetContent($serverName)
    {
        if ($serverName === 'phat-son') {
            $data = $this->getTopUserList('Phat-Son15');
        } elseif ($serverName === 'tung-son') {
            $data = $this->getTopUserList('Tung-Son14');
        }
        $data = $this->convertArrayToPaginate($data, self::PAGE_SIZE_HOME_RANKING);

        return view('t2g_common::voyager.partials.top_user_list', ['data' => $data]);
    }
    /**
     * @return array
     *
     */
    public function getTopUserList($name)
    {
        $data = [
          1 => [
              'user' => $name,
              'char' => 'cha1',
              'level' => 375,
              'exp' => 188989100
          ],
          2 => [
              'user' => $name,
              'char' => 'cha2',
              'level' => 375,
              'exp' => 1887670768
          ],
          3 => [
              'user' => $name,
              'char' => 'cha3',
              'level' => 375,
              'exp' => 1887670768
          ],
          4 => [
              'user' => $name,
              'char' => 'cha4',
              'level' => 375,
              'exp' => 1887670768
          ],
          5 => [
              'user' => $name,
              'char' => 'cha5',
              'level' => 375,
              'exp' => 1887670768
          ],
          6 => [
              'user' => $name,
              'char' => 'cha6',
              'level' => 375,
              'exp' => 1887670768
          ], 7 => [
                'user' => 'phatson',
                'char' => 'cha7',
                'level' => 375,
                'exp' => 1887670768
            ],
          8 => [
              'user' => 'phatson',
              'char' => 'cha8',
              'level' => 375,
              'exp' => 1887670768
          ],
          9 => [
              'user' => 'phatson',
              'char' => 'cha9',
              'level' => 375,
              'exp' => 1887670768
          ],
          10 => [
              'user' => 'phatson0',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          11 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          12 => [
              'user' => 'phatson',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          13 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          14 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          15 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          16 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          10 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
            ,
          17 => [
              'user' => 'ma10',
              'char' => 'cha10',
              'level' => 375,
              'exp' => 1887670768
          ]
        ];
        return $data;
    }

    /**
     * @return string
     */
    protected function getViewPermission()
    {
        return 'widget.user';
    }

    /**
     * @param $items
     * @param  int  $perPage
     * @param  null  $page
     * @param  array  $options
     * @return LengthAwarePaginator
     */
    public function convertArrayToPaginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
