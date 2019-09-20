<?php

namespace T2G\Common\Services;

use GuzzleHttp\Psr7\Response;
use Illuminate\Log\LogManager;

/**
 * Class MockedJXApiClient
 */
class MockedJXApiClient
{
    /**
     * @var LogManager
     */
    protected $logger;

    /**
     * MockedJXApiClient constructor.
     */
    public function __construct()
    {
        $this->logger = app(LogManager::class);
        $this->logger->channel(config('t2g_common.log.jx_api_channel'));
    }

    /**
     * @param       $uri
     * @param array $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function get($uri, array $options = [])
    {
        $this->logger->info("GET Request to JX API", [
            'uri'     => $uri,
            'options' => $options,
        ]);

        return new Response(200, [], "1: Success");
    }

    /**
     * @param       $uri
     * @param array $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function post($uri, array $options = [])
    {
        $this->logger->info("POST Request to JX API", [
            'uri'     => $uri,
            'options' => $options,
        ]);

        return new Response(200, [], "1: Success");
    }
}
