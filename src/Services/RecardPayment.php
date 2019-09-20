<?php

namespace T2G\Common\Services;

use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Util\MobileCard;
use Illuminate\Http\Request;

/**
 * Class RecardPayment
 *
 * @package \T2G\Common\Services
 */
class RecardPayment extends AbstractCardPayment
{
    const ENDPOINT = "/api/card";
    const BASE_URL = "https://recard.vn";
    const CARD_TYPE_VIETTEL = 1;
    const CARD_TYPE_MOBIFONE = 2;
    const CARD_TYPE_VINAPHONE = 3;

    public static $callbackReason = [
        1 => "Thẻ không tồn tại",
        2 => "Thẻ đã được sử dụng",
        3 => "Thẻ không sử dụng được",
        4 => "Thẻ sai mệnh giá",
    ];

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * RecardPayment constructor.
     *
     * @param $merchantId
     * @param $secretKey
     */
    public function __construct($merchantId, $secretKey)
    {
        parent::__construct();
        $this->merchantId = $merchantId;
        $this->secretKey = $secretKey;
        if (config('t2g_common.payment.card_payment_mocked')) {
            $this->client = new CardPaymentApiClientMocked();
        } else {
            $this->client = new \GuzzleHttp\Client([
                'base_uri'    => self::BASE_URL,
                'timeout'     => 60,
                'http_errors' => false,
            ]);
        }
    }

    /**
     * @return string
     */
    public function getPartnerName()
    {
        return CardPaymentInterface::PARTNER_RECARD;
    }

    /**
     * @inheritdoc
     */
    public function useCard(MobileCard $card, $paymentId = '')
    {
        $signature = $this->sign($card);
        $params['form_params'] = [
            'merchant_id' => $this->merchantId,
            'secret_key'  => $this->secretKey,
            'type'        => $this->getCardType($card),
            'serial'      => $card->getSerial(),
            'code'        => $card->getCode(),
            'amount'      => $card->getAmount(),
            'signature'   => $signature,
        ];
        $response = $this->client->post(self::ENDPOINT, $params);

        return new RecardResponse($response, $card);
    }

    /**
     * @param $card
     *
     * @return string
     */
    private function sign(MobileCard $card)
    {
        $type = $this->getCardType($card);
        $data = $this->merchantId . $type . $card->getSerial() . $card->getCode() . $card->getAmount();

        return hash_hmac('sha256', $data, $this->secretKey);
    }

    /**
     * @param \T2G\Common\Util\MobileCard $card
     *
     * @return int
     */
    private function getCardType(MobileCard $card)
    {
        $type = strtoupper($card->getType());
        switch ($type) {
            case MobileCard::TYPE_VIETTEL:
                return self::CARD_TYPE_VIETTEL;
            case MobileCard::TYPE_MOBIFONE:
                return self::CARD_TYPE_MOBIFONE;
            case MobileCard::TYPE_VINAPHONE:
                return self::CARD_TYPE_VINAPHONE;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getCallbackMessage($callbackCode)
    {
        return isset(self::$callbackReason[$callbackCode]) ? self::$callbackReason[$callbackCode] : "Lỗi không xác định `{$callbackCode}`";
    }

    /**
     * @inheritdoc
     */
    public function getTransactionCodeFromCallback(Request $request)
    {
        $secretKey = $request->get('secret_key');
        if ($secretKey != $this->secretKey) {
            return '';
        }

        return $request->get('transaction_code');
    }

    /**
     * @inheritdoc
     */
    public function parseCallbackRequest(Request $request)
    {
        $status = intval($request->get('status')) === 1 ? true : false;
        $reason = $request->get('reason');
        $amount = intval($request->get('amount'));

        return [$status, $amount, $reason];
    }
}
