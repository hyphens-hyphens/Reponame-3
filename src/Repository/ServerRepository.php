<?php

namespace T2G\Common\Repository;

use Prettus\Repository\Eloquent\BaseRepository;
use T2G\Common\Models\Server;

/**
 * Class ServerRepository
 *
 * @package \App\Repository
 */
class ServerRepository extends AbstractEloquentRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Server::class;
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function findByGameServerId($id)
    {
        $query = $this->query();
        $query->whereGameServerId($id)
            ->active()
        ;

        return $query->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]
     */
    public function getAvailableServers()
    {
        $query = $this->query();
        $query->active();

        return $query->get();
    }

    /**
     * @return Server|null
     */
    public function getServerPlayNow()
    {
        $server = $this->find(setting('site.server_play_now'));
        if (!$server) {
            $query = $this->query();
            $server = $query->active()
                            ->orderBy('game_server_id', 'DESC')
                            ->first()
            ;
        }

        return $server;
    }
}
