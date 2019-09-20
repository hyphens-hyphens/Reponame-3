<?php

namespace T2G\Common\Services;

use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Util\MobileCard;
use Illuminate\Http\Request;

/**
 * Class NapTheNhanhPayment
 *
 * @package \T2G\Common\Services
 */
class NapTheNhanhPayment extends AbstractCardPayment
{
    const ENDPOINT = "/api/charging-wcb";
    const BASE_URL = "http://sys.napthenhanh.com";
    const CARD_TYPE_VIETTEL = 'VIETTEL';
    const CARD_TYPE_MOBIFONE = 'MOBIFONE';
    const CARD_TYPE_VINAPHONE = 'VINAPHONE';

    public static $callbackReason = [
        0 => "Thẻ không hợp lệ",
        2 => "Thẻ đang chờ xử lý",
        3 => "Thẻ sai mệnh giá",
    ];

    /**
     * @var string
     */
    private $partnerId;

    /**
     * @var string
     */
    private $partnerKey;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct($partnerId, $partnerKey)
    {
        parent::__construct();
        $this->partnerId = $partnerId;
        $this->partnerKey = $partnerKey;
        if (config('t2g_common.payment.is_mocked')) {
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
        return CardPaymentInterface::PARTNER_NAPTHENHANH;
    }

    /**
     * @inheritdoc
     */
    public function useCard(MobileCard $card, $paymentId = '')
    {
        $signature = $this->sign($card, $paymentId);
        $params['form_params'] = [
            'partner_id' => $this->partnerId,
            'type'       => $this->getCardType($card),
            'serial'     => $card->getSerial(),
            'pin'        => $card->getCode(),
            'amount'     => $card->getAmount(),
            'sign'       => $signature,
            'tranid'     => $paymentId,
        ];
        $response = $this->client->post(self::ENDPOINT, $params);

        return new NapTheNhanhResponse($response, $card);
    }

    /**
     * @param \T2G\Common\Util\MobileCard $card
     * @param                      $paymentId
     *
     * @return string
     */
    private function sign(MobileCard $card, $paymentId)
    {
        $type = $this->getCardType($card);
        $signature = $this->partnerId . $this->partnerKey . $type . $card->getCode() . $card->getSerial() . $card->getAmount() . $paymentId;

        return md5($signature);
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
        return $request->get('tranid');
    }

    /**
     * @inheritdoc
     */
    public function parseCallbackRequest(Request $request)
    {
        $status = intval($request->get('status')) == 1 ? true : false;
        $responseCode = $request->get('status');
        $amount = intval($request->get('amount'));

        return [$status, $amount, $responseCode];
    }
}
