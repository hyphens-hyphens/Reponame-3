<?php

namespace T2G\Common\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\HttpFoundation\Request;
use T2G\Common\Exceptions\GameApiException;
use T2G\Common\Util\GameApiLog;

/**
 * Class JXApiClient
 */
class JXApiClient extends Client
{
    const ENDPOINT_CREATE_USER            = '/api/register.php';
    const ENDPOINT_SET_PASSWORD           = '/api/changepass1.php';
    const ENDPOINT_SET_SECONDARY_PASSWORD = '/api/changepass2.php';
    const ENDPOINT_ADD_GOLD               = '/api/donate.php';
    const ENDPOINT_CCU                    = '/api/ccu.php';

    /**
     * @var array
     */
    private $baseUrls;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var \T2G\Common\Util\GameApiLog
     */
    protected $logger;

    /**
     * JXApiClient constructor.
     *
     * @param $baseUrls
     * @param $apiKey
     */
    public function __construct($baseUrls, $apiKey)
    {
        parent::__construct([
            'http_errors' => false,
            'timeout'     => 2,
        ]);
        $this->baseUrls = $baseUrls;
        $this->apiKey = $apiKey;
        $this->logger = app(GameApiLog::class);
    }

    /**
     * @param $username
     * @param $password
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function createUser($username, $password)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'mk' => $password, 'sign' => $signed];
        $response = $this->get(self::ENDPOINT_CREATE_USER, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot create account for user `{$username}`",
                ['api_response' => $body]
            );

            return false;
        }

        return true;
    }

    /**
     * @param $username
     * @param $newPassword
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function setPassword($username, $newPassword)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'mk' => $newPassword, 'sign' => $signed];
        $response = $this->get(self::ENDPOINT_SET_PASSWORD, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot set password for user `{$username}`",
                ['api_response' => $body]
            );

            return false;
        }

        return true;
    }

    /**
     * @param $username
     * @param $newSecondaryPassword
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function setSecondaryPassword($username, $newSecondaryPassword)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'mk' => $newSecondaryPassword, 'sign' => $signed];
        $response = $this->get(self::ENDPOINT_SET_SECONDARY_PASSWORD, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot set password 2 for user `{$username}`",
                ['api_response' => $body]
            );

            return false;
        }

        return true;
    }

    /**
     * @param     $username
     * @param int $knb
     * @param int $xu
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function addGold($username, $knb = 0, $xu = 0)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'sign' => $signed];
        if ($xu > 0) {
            $params['soxu'] = intval($xu);
        }
        if ($knb > 0) {
            $isLegacyApi = config('t2g_common.game_api.legacy', true);
            $params['knb'] = $isLegacyApi ? round($knb / 20) : $knb;
        }
        $response = $this->get(self::ENDPOINT_ADD_GOLD, [
            'query' => $params
        ]);

        $body = $response->getBody()->getContents();
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot add gold for user `{$username}`",
                ['api_response' => $body, 'knb' => $knb, 'xu' => $xu]
            );

            return false;
        }

        return true;
    }

    /**
     * @return array
     *         ["Server name" => 234 // CCU, ...]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function getCCUs()
    {
        $response = $this->get(self::ENDPOINT_CCU);
        $body = $response->getBody()->getContents();
        $CCUs = json_decode($body, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $CCUs;
        }

        $this->logger->notice(
            "Cannot get CCUs",
            ['api_response' => $body]
        );

        return [];
    }

    /**
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param array                                 $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function get($uri, $options = [])
    {
        return $this->_request(Request::METHOD_GET, $uri, $options);
    }

    /**
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param array                                 $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function post($uri, $options = [])
    {
        return $this->_request(Request::METHOD_POST, $uri, $options);
    }

    /**
     * @param       $method
     * @param       $uri
     * @param array $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    private function _request($method, $uri, array $options = [])
    {
        if (config('t2g_common.game_api.is_mocked', false)) {
            return app(MockedJXApiClient::class)->{$method}($uri, $options);
        }

        try {
            $baseUri = rtrim(current($this->baseUrls), '/');
            $response = $this->request($method, $baseUri . $uri, $options);
        } catch (ConnectException $e) {
            $next = next($this->baseUrls);
            if ($next) {
                return $this->_request($method, $uri, $options);
            }
            $retries = count($this->baseUrls);
            $timeout = $retries * config('t2g_common.game_api.timeout', 10);
            if (count($this->baseUrls) > 1) {
                throw new GameApiException("Cannot connect to API server after retried {$retries} hosts for {$timeout} seconds.");
            }

            throw $e;
        }
        reset($this->baseUrls);

        return $response;
    }
}
