<?php

namespace T2G\Common\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use T2G\Common\Exceptions\GameApiException;
use T2G\Common\Models\GiftCode;

/**
 * Class JXApiClient
 */
class JXApiClient implements GameApiClientInterface
{
    const ENDPOINT_CREATE_USER            = '/api/register.php';
    const ENDPOINT_SET_PASSWORD           = '/api/changepass1.php';
    const ENDPOINT_SET_SECONDARY_PASSWORD = '/api/changepass2.php';
    const ENDPOINT_ADD_GOLD               = '/api/donate.php';
    const ENDPOINT_CCU                    = '/api/ccu.php';
    const ENDPOINT_USER_LAST_LOGIN        = '/api/user_last_login.php';
    const ENDPOINT_ADD_CODE               = '/api/add_code.php';

    static $responseStack = [];
    /**
     * @var array
     */
    private $baseUrls;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $lastResponseText;

    /**
     * JXApiClient constructor.
     *
     * @param $baseUrls
     * @param $apiKey
     */
    public function __construct($baseUrls, $apiKey)
    {
        $this->baseUrls = $baseUrls;
        $this->apiKey = $apiKey;
        $this->logger = app('game_api_log');
    }

    /**
     * @return array
     */
    private function getClientDefaultConfigs()
    {
        return [
            'http_errors' => false,
            'timeout'     => config('t2g_common.game_api.timeout', 10),
        ];
    }

    /**
     * @param $baseUrl
     *
     * @return \GuzzleHttp\Client
     */
    private function makeClient($baseUrl)
    {
        $configs = $this->getClientDefaultConfigs() + ['base_uri' => $baseUrl];

        return new Client($configs);
    }

    /**
     * @return string|null
     */
    public function getLastResponse()
    {
        return $this->lastResponseText;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return string
     */
    protected function getResponseText(ResponseInterface $response)
    {
        $responseText = $response->getBody()->getContents();
        $this->lastResponseText = $responseText;

        return $this->lastResponseText;
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
        $body = $this->getResponseText($response);
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
        $body = $this->getResponseText($response);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot set password for user `{$username}`. " . $this->getResponseError($response, $body),
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
        $body = $this->getResponseText($response);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot set password 2 for user `{$username}`. " . $this->getResponseError($response, $body),
                ['api_response' => $body]
            );

            return false;
        }

        return true;
    }

    /**
     * @param      $username
     * @param int  $knb
     * @param int  $xu
     * @param mixed|null $orderId
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function addGold($username, $knb = 0, $xu = 0, $orderId = null)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'sign' => $signed, 'orderId' => $orderId];
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

        $body = $this->getResponseText($response);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            $this->logger->critical(
                "Cannot add gold for user `{$username}`. " . $this->getResponseError($response, $body),
                ['api_response' => $body, 'knb' => $knb, 'xu' => $xu]
            );

            return false;
        }

        return true;
    }

    /**
     * @return array
     *         ["Server name" => 234 // CCU, ...]
     */
    public function getCCUs()
    {
        try {
            $response = $this->get(self::ENDPOINT_CCU);
            $body = $this->getResponseText($response);
            $CCUs = json_decode($body, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $CCUs;
            }
        } catch (\Exception $e) {
            $this->logger->notice(
                "Cannot get CCUs",
                ['api_response' => $this->getLastResponse()]
            );
        }

        return [];
    }

    /**
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param array                                 $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \T2G\Common\Exceptions\GameApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function get($uri, $options = [])
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
    private function post($uri, $options = [])
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
            $baseUrl = rtrim(current($this->baseUrls), '/');
            $requestUrl = $baseUrl . $uri;
            $this->logRequest($method, $requestUrl, $options);
            $client = $this->makeClient($baseUrl);
            $response = $client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
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

    private function logRequest($method, $uri, array $options)
    {
        $this->logger->info("Requesting to JX API", [
            'method' => $method,
            'uri' => $uri,
            'options' => $options
        ]);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param                                     $body
     *
     * @return string
     */
    private function getResponseError(ResponseInterface $response, $body)
    {
        if ($response->getStatusCode() == Response::HTTP_INTERNAL_SERVER_ERROR) {
            return $response->getReasonPhrase();
        }

        return $body;
    }

    /**
     * @param \DateTime|null $date
     *
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     * @throws \Exception
     */
    public function getUsersLastLogin(?\DateTime $date = null)
    {
        $date = $date ?: new \DateTime();
        $params = ['from' => $date->format('Y-m-d')];
        $response = $this->get(self::ENDPOINT_USER_LAST_LOGIN, $params);
        $body = $this->getResponseText($response);
        if ($response->getStatusCode() != Response::HTTP_OK) {
            $this->logger->info(
                "Cannot get list of last login users. " . $this->getResponseError($response, $body),
                ['api_response' => $body]
            );

            return [];
        }

        return json_decode($body, 1);
    }

    /**
     * @param                             $username
     * @param \T2G\Common\Models\GiftCode $giftCode
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \T2G\Common\Exceptions\GameApiException
     */
    public function addGiftCode($username, GiftCode $giftCode)
    {
        $sign = md5($this->apiKey . $giftCode->code_name . $username);
        $response = $this->post(self::ENDPOINT_ADD_CODE, [
            'form_params' => ['code' => $giftCode->code_name, 'username' => $username, 'sign' => $sign]
        ]);
        $body = $this->getResponseText($response);
        $data = json_decode($body, true);
        if ($response->getStatusCode() != Response::HTTP_OK || empty($data['success'])) {
            $this->logger->critical(
                "Cannot add code `{$giftCode->code_name}` for user `{$username}`. ",
                ['response' => $this->getLastResponse()]
            );
            return false;
        }

        return true;
    }
}
