<?php

namespace T2G\Common\Util;

use Illuminate\Log\LogManager;

/**
 * Class GameApiLog
 *
 * @package \T2G\Common\Util
 */
class GameApiLog extends LogManager
{
    /**
     * GameApiLog constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(\Illuminate\Foundation\Application $app)
    {
        parent::__construct(($app));

        $this->channel(config('t2g_common.game_api.is_mocked') ? 'game_api_request' : 'game_api');
    }

}
