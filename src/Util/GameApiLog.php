<?php

namespace T2G\Common\Util;

use Illuminate\Log\LogManager;

/**
 * Class GameApiLog
 *
 * @package \T2G\Common\Util
 */
class GameApiLog
{
    /**
     * @var \Illuminate\Log\LogManager
     */
    protected $logger;

    /**
     * GameApiLog constructor.
     */
    public function __construct()
    {
        $this->logger = app(LogManager::class);
        $this->logger->channel(config('t2g_common.game_api.is_mocked') ? config('t2g_common.log.jx_api_channel') : 'game_api');
    }

    /**
     * @param       $message
     * @param array $data
     */
    public function notify($message, $data = [])
    {
        $message = "[" . time() . "] " . $message;
        $this->logger->notice($message, $data);
    }
}
