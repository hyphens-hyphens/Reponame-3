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
        $this->logger->channel(env('GAME_API_MOCK') ? 'game_api_request' : 'game_api');
    }

    /**
     * @param       $message
     * @param array $data
     */
    public function notify($message, $data = [])
    {
        $message = "[" . time() . "] " . $message;
        $this->logger->critical($message, $data);
    }
}
