<?php

namespace T2G\Common\Controllers\Front;

use Illuminate\Http\Request;
use T2G\Common\Contract\CardPaymentInterface;
use T2G\Common\Models\Payment;
use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\NapTheNhanhPayment;
use T2G\Common\Services\RecardPayment;
use T2G\Common\Services\SmsNotifierParser;
use T2G\Common\Util\MobileCard;

/**
 * Class PaymentController
 *
 * @package \T2G\Common\Controllers\Front
 */
class PaymentController extends BaseFrontController
{
    protected $discord;

    public function __construct()
    {
        parent::__construct();
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.payment_alert'));
    }

    public function index()
    {
       return redirect(route('front.static.nap_the_cao'));
    }

    /**
     * @param \T2G\Common\Repository\PaymentRepository $paymentRepository
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function submitCard(PaymentRepository $paymentRepository)
    {
        $user = \Auth::user();
        if (!$user) {
            return response()->json(["error" => 'Vui lòng đăng nhập lại để tiếp tục thao tác', 'relogin' => true]);
        }
        if ($this->isInMaintenancePeriod()) {
            return response()->json(["error" => 'Server đang trong thời gian bảo trì định kỳ, vui lòng thử lại sau 17:00H.']);
        }

        $card = $this->createCardInstance();
        $error = $this->validateCard($card, $paymentRepository);
        if ($error) {
            return response()->json(['error' => $error]);
        }
        $cardPayment = $this->getCardPaymentService();
        list($knb, $soxu) = $paymentRepository->exchangeGamecoin($card->getAmount(), Payment::PAYMENT_TYPE_CARD);
        $payment = $paymentRepository->createCardPayment($user, $card, $knb, $this->getPayMethod($card, $cardPayment));
        if ($card->getType() == MobileCard::TYPE_ZING){
            $this->discord->send("`{$user->name}` vừa submit 1 thẻ Zing `" . $card->getAmount() / 1000 . "k`");
        } else {
            $result = $cardPayment->useCard($card, $payment->getKey());
            if ($result->isSuccess() && $transactionCode = $result->getTransactionCode()) {
                $paymentRepository->updateCardPayment($payment, $transactionCode);
            } else {
                $cardPayment->logCardPaymentError($result);
                return response()->json(["error" => implode('<br/>', $result->getErrors())]);
            }
        }

        return response()->json(["msg" => 'Thẻ đang được xử lý... Vui lòng đợi vài phút, hệ thống sẽ tự cộng Xu nếu xử lý thành công.']);
    }

    /**
     * @param \T2G\Common\Util\MobileCard              $card
     * @param \T2G\Common\Repository\PaymentRepository $paymentRepository
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function validateCard(MobileCard $card, PaymentRepository $paymentRepository)
    {
        if(!$card->getCode() || !$card->getSerial() || !$card->getType() || !$card->getAmount()){
            return "Vui lòng điền đầy đủ thông tin.";
        }
        // check đúng định dạng the Mobi: seri 15, ma 12. Zing: seri 12, ma:9. vcoin 12-12
        $checkCardFormat = true;
        if ($card->getType() == MobileCard::TYPE_MOBIFONE) {
            if (strlen($card->getCode()) != 12 || strlen($card->getSerial()) != 15) {
                $checkCardFormat = false;
            }
        }
        if ($card->getType() == MobileCard::TYPE_ZING) {
            if (strlen($card->getCode()) < 9 || strlen($card->getSerial()) != 12) {
                $checkCardFormat = false;
            }
        }
        if ($card->getType() == MobileCard::TYPE_VINAPHONE) {
            if (strlen($card->getCode()) < 12 || strlen($card->getSerial()) < 12) {
                $checkCardFormat = false;
            }
        }
        if ($card->getType() == MobileCard::TYPE_VIETTEL) {
            if (strlen($card->getCode()) < 12 || strlen($card->getSerial()) < 11) {
                $checkCardFormat = false;
            }
        }
        if (!$checkCardFormat) {
            return "Thẻ định dạng không đúng. Vui lòng kiểm tra lại.";
        }
        if($paymentRepository->isCardExisted($card)){
            return  "Thẻ đã có trong hệ thống.";
        }

        return false;
    }

    public function cardPaymentCallback(PaymentRepository $paymentRepository, Request $request)
    {
        $gameApiClient = getGameApiClient();
        $this->getCardPaymentService();
        $cardPayment = $this->getCardPaymentServiceForCallback($request);
        $cardPayment->logCallbackRequest($request);
        $transactionCode = $cardPayment->getTransactionCodeFromCallback($request);
        if (!$transactionCode) {
            return $this->responseForCallback($cardPayment, "No transaction code found");
        }
        $record = $paymentRepository->getByTransactionCode($transactionCode);
        if (!$record) {
            return $this->responseForCallback($cardPayment, "Transaction not found", 404);
        }
        if (!empty($record->status)) {
            return $this->responseForCallback($cardPayment, "Transaction was processed successfully before");
        }

        // add gold
        $responseStatus = false;
        list($status, $amount, $callbackCode) = $cardPayment->parseCallbackRequest($request);
        $paymentRepository->updateCardPaymentTransaction($record, $status, $cardPayment->getCallbackMessage($callbackCode), $amount);
        if ($status && empty($record->gold_added)) {
            $gamecoin = $record->gamecoin;
            $result = $gameApiClient->addGold($record->username, $gamecoin, 0, $record->id);
            $paymentRepository->updateRecordAddedGold($record, $result);
            $responseStatus = true;
        }

        return $this->responseForCallback($cardPayment, "Processed", 200, $responseStatus);
    }

    /**
     * @return \T2G\Common\Util\MobileCard
     */
    private function createCardInstance()
    {
        $type   = trim(request('card_type'));
        $amount = intval(trim(request('card_amount')));
        $serial = str_replace(" ","",trim(request('card_serial')));
        $serial = str_replace("-","",$serial);
        $pin = str_replace(" ","",trim(request('card_pin')));
        $pin = str_replace("-","",$pin);
        $card = new MobileCard();
        $card->setType($type)
            ->setCode($pin)
            ->setSerial($serial)
            ->setAmount($amount)
        ;

        return $card;
    }

    /**
     * Received Internet Banking SMS alert from T2G_Notifier Android app and send to Discord webhook
     *
     * @param \T2G\Common\Services\SmsNotifierParser $parser
     */
    public function alertTransaction(SmsNotifierParser $parser)
    {
        $message = request('message');
        $createdAt = request('createdAt');
        if (!$message) {
            exit();
        }
        \Log::info("SMS Received.", [$message, $createdAt]);
        $stkDongA = config('t2g_common.payment.banking_account_dong_a');
        $stkVCB = config('t2g_common.payment.banking_account_vietcombank');
        if ($stkDongA && strpos($message, "TK {$stkDongA}") !== false) {
            $alert = $parser->parseDongABankSms($message, $createdAt);
        } elseif ($stkVCB && strpos($message, "TK {$stkVCB}") !== false) {
            $alert = $parser->parseVietcomBankSms($stkVCB, $message, $createdAt);
        } else {
            $alert = $parser->parseFptShopSms($message, $createdAt);
        }

        if ($alert && !$parser->isSkippedMessage($message)) {
            $this->discord->send($alert);
        }
    }

    /**
     * @return bool
     */
    private function isInMaintenancePeriod()
    {
        $now = time();
        $startMaintenance = (\DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d 16:25')))->getTimestamp();
        $endMaintenance = (\DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d 16:55')))->getTimestamp();

        return $now > $startMaintenance && $now < $endMaintenance;
    }

    /**
     * @return CardPaymentInterface
     */
    private function getCardPaymentService()
    {
        $autoSwitch = boolval(voyager()->setting('site.card_payment_auto_switch', false));
        // auto switch handle
        $hour = intval(date('G'));
        if ($autoSwitch && ($hour > 21 || $hour < 9)) {
            return app(config('t2g_common.payment.card_payment_partner_pos2', NapTheNhanhPayment::class));
        }

        return app(CardPaymentInterface::class);
    }

    /**
     * Create CardPaymentInterface based on callback request
     * @param \Illuminate\Http\Request $request
     * @return CardPaymentInterface
     */
    private function getCardPaymentServiceForCallback(Request $request)
    {
        if ($request->get('secret_key') && $request->get('transaction_code')) {
            return app(RecardPayment::class);
        }
        if ($request->get('tranid')) {
            return app(NapTheNhanhPayment::class);
        }

        return app(CardPaymentInterface::class);
    }

    /**
     * @param \T2G\Common\Contract\CardPaymentInterface $cardPayment
     * @param                                           $message
     * @param int                                       $statusCode
     * @param bool                                      $responseStatus
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function responseForCallback(CardPaymentInterface $cardPayment, $message, $statusCode = 200, $responseStatus = false)
    {
        $response = [
            'status'  => $responseStatus,
            'message' => $message,
        ];
        $cardPayment->logCallbackProcessed($message);

        return response()->json($response, $statusCode);
    }

    /**
     * get pay_method from Card payment partner
     *
     * @param \T2G\Common\Util\MobileCard               $card
     * @param \T2G\Common\Contract\CardPaymentInterface $cardPayment
     *
     * @return string
     */
    private function getPayMethod(MobileCard $card, CardPaymentInterface $cardPayment)
    {
        if ($card->getType() == MobileCard::TYPE_ZING) {
            return Payment::PAY_METHOD_ZING_CARD;
        }

        return $cardPayment->getPartnerName() == CardPaymentInterface::PARTNER_NAPTHENHANH ? Payment::PAY_METHOD_NAPTHENHANH : Payment::PAY_METHOD_RECARD;
    }
}
