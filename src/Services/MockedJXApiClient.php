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
        $this->logger = $this->logger->channel('game_api_request');
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
        $response = "1: Success";
        if (strpos($uri, JXApiClient::ENDPOINT_CCU) !== false) {
            $response = json_encode(['N/A' => 0]);
        }

        return new Response(200, [], $response);
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
        $response = "1: Success";
        if (strpos($uri, JXApiClient::ENDPOINT_ADD_CODE) !== false) {
            $response = json_encode([
                'success'     => true,
                'message'     => "Add code thành công.",
                'status_code' => 1,
            ]);
        }

        return new Response(200, [], $response);
    }
}
