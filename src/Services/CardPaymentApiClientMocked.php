<?php

namespace T2G\Common\Services;

use Illuminate\Log\LogManager;
use T2G\Common\Contract\CardPaymentInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CardPaymentApiClientMocked
 *
 * @package \T2G\Common\Services
 */
class CardPaymentApiClientMocked
{
    protected static $mockedResponse;

    /**
     * @var LogManager
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = app(LogManager::class);
        $this->logger->channel('card_payment_mocked');
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $response
     */
    public static function setMockedResponse(Response $response)
    {
        self::$mockedResponse = $response;
    }

    public function get($uri, array $options = [])
    {
        $this->logger->info("GET Request to Card Payment API", [
            'uri'     => $uri,
            'options' => $options,
        ]);

        return $this->response();
    }

    public function post($uri, array $options = [])
    {
        $this->logger->info("POST Request to Card Payment API", [
            'uri'     => $uri,
            'options' => $options,
        ]);

        return $this->response();
    }

    /**
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function response()
    {
        if (self::$mockedResponse instanceof ResponseInterface) {
            $response = self::$mockedResponse;
        } else {
            if (env('CARD_PAYMENT_PARTNER') == CardPaymentInterface::PARTNER_NAPTHENHANH) {
                $response = new Response(200, [], json_encode([
                    'tranid'  => time(),
                    'status'  => NapTheNhanhResponse::RESPONSE_STATUS_SUCCESS,
                    'message' => 'Thẻ đã được nạp thành công và đang chờ xử lý '
                    ])
                );
            } else {
                $response = new Response(200, [], json_encode(['transaction_code' => time(), 'success' => true]));
            }

        }

        return $response;
    }
}
