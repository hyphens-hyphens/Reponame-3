<?php

namespace T2G\Common\Services;

use T2G\Common\Util\MobileCard;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RecardResponse
 *
 * @package \T2G\Common\Services
 */
class RecardResponse extends AbstractCardPaymentResponse
{
    public static $callbackReason = [
        1 => "Thẻ không tồn tại",
        2 => "Thẻ đã được sử dụng",
        3 => "Thẻ sai mệnh giá",
        4 => "Thẻ sai mệnh giá",
    ];

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $transactionCode;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param $reason
     *
     * @return mixed|string
     */
    public function getCallbackMessage($reason)
    {
        return isset(self::$callbackReason[$reason]) ? self::$callbackReason[$reason] : "Unknown reason `{$reason}`";
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \T2G\Common\Util\MobileCard                $card
     */
    protected function parseResponse(ResponseInterface $response, MobileCard $card)
    {
        $this->statusCode = $response->getStatusCode();
        $this->body = $response->getBody()->getContents();
        $result = json_decode($this->body, 1);
        if ($this->statusCode != 200) {
            $this->logger->info("ReCard response with error", [
                'card'       => $card,
                'response'   => $this->body,
                'statusCode' => $this->statusCode,
            ]);
            if (isset($result['statusCode'])) {
                unset($result['statusCode']);
            }
            foreach ($result as $field => $error) {
                $this->errors = array_merge($this->errors, $error);
            }
        }
        if ($this->statusCode == 200 && !empty($result['success']) && !empty($result['transaction_code'])) {
            $this->success = true;
            $this->transactionCode = $result['transaction_code'];
        }
    }
}
