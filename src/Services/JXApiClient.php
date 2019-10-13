<?php

namespace T2G\Common\Services;

use GuzzleHttp\Client;

/**
 * Class JXApiClient
 */
class JXApiClient
{
    const ENDPOINT_CREATE_USER            = '/api/register.php';
    const ENDPOINT_SET_PASSWORD           = '/api/changepass1.php';
    const ENDPOINT_SET_SECONDARY_PASSWORD = '/api/changepass2.php';
    const ENDPOINT_ADD_GOLD               = '/api/donate.php';
    const ENDPOINT_CCU                    = '/api/ccu.php';

    static $responseStack = [];

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $apiKey;

    public function __construct($baseUrl, $apiKey)
    {
        if (config('t2g_common.game_api.is_mocked', false)) {
            $this->client = new MockedJXApiClient();
        } else {
            $this->client = new Client([
                'base_uri' => $baseUrl
            ]);
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $response
     */
    protected function addResponseStack(?string $response)
    {
        array_push(self::$responseStack, $response);
    }

    /**
     * @return string|null
     */
    public function getLastResponse()
    {
        return count(self::$responseStack) ? array_pop(self::$responseStack) : null;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return bool
     */
    public function createUser($username, $password)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'mk' => $password, 'sign' => $signed];
        $response = $this->client->get(self::ENDPOINT_CREATE_USER, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $this->addResponseStack($body);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            return false;
        }

        return true;
    }

    /**
     * @param $username
     * @param $newPassword
     *
     * @return bool
     */
    public function setPassword($username, $newPassword)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'mk' => $newPassword, 'sign' => $signed];
        $response = $this->client->get(self::ENDPOINT_SET_PASSWORD, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $this->addResponseStack($body);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
            return false;
        }

        return true;
    }

    /**
     * @param $username
     * @param $newSecondaryPassword
     *
     * @return bool
     */
    public function setSecondaryPassword($username, $newSecondaryPassword)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'mk' => $newSecondaryPassword, 'sign' => $signed];
        $response = $this->client->get(self::ENDPOINT_SET_SECONDARY_PASSWORD, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $this->addResponseStack($body);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
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
     */
    public function addGold($username, $knb = 0, $xu = 0)
    {
        $signed = md5($this->apiKey . $username);
        $params = ['tk' => $username, 'sign' => $signed];
        if ($xu > 0) {
            $params['soxu'] = $xu;
        }
        if ($knb > 0) {
            $params['knb'] = $knb;
        }
        $response = $this->client->get(self::ENDPOINT_ADD_GOLD, [
            'query' => $params
        ]);
        $body = $response->getBody()->getContents();
        $this->addResponseStack($body);
        $responseCode = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $body);
        if(substr($responseCode, 0, 2) != '1:') {
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
        $response = $this->client->get(self::ENDPOINT_CCU);
        $body = $response->getBody()->getContents();
        $this->addResponseStack($body);
        $CCUs = json_decode($body, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $CCUs;
        }

        return [];
    }
}
