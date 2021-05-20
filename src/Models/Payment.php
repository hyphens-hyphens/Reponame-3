<?php
namespace T2G\Common\Models;

use T2G\Common\Util\MobileCard;

/**
 * Class AbstractPayment
 *
 * @property int $id
 * @property string|null $card_pin
 * @property string|null $card_serial
 * @property string|null $card_type
 * @property string|null $transaction_id
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string|null $pay_method
 * @property string|null $pay_from
 * @property string|null $expired_date
 * @property int|null $user_id
 * @property string|null $username
 * @property int|null $server_id
 * @property int|null $payment_type
 * @property int|null $card_amount
 * @property int|null $gamecoin
 * @property int|null $gamecoin_promotion
 * @property int $status
 * @property int $finished
 * @property int $gold_added
 * @property int $gateway_status
 * @property string|null $gateway_response
 * @property string|null $gateway_amount
 * @property string|null $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $note
 * @property int|null $creator_id
 * @property int|null $amount
 * @property int|null $status_code
 * @property int|null $profit
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel active()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCardAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCardPin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCardSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereExpiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGamecoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGamecoinPromotion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGatewayAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGatewayResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGatewayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGoldAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUtmCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUtmMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUtmSource($value)
 * @mixin \Eloquent
 * @property-read \T2G\Common\Models\AbstractUser|null $creator
 * @property-read \T2G\Common\Models\AbstractUser|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\T2G\Common\Models\Payment query()
 */
class Payment extends BaseEloquentModel
{
    const PAYMENT_TYPE_CARD = 1;
    const PAYMENT_TYPE_MOMO = 3;
    const PAYMENT_TYPE_BANK_TRANSFER = 4;
    const PAYMENT_TYPE_ADVANCE_DEBT = 5;
    const PAYMENT_TYPE_ADD_XU_CTV = 6;

    const PAYMENT_STATUS_SUCCESS                 = 1;
    const PAYMENT_STATUS_PROCESSING              = 2;
    const PAYMENT_STATUS_MANUAL_ADD_GOLD_ERROR   = 3;
    const PAYMENT_STATUS_GATEWAY_RESPONSE_ERROR  = 4;
    const PAYMENT_STATUS_GATEWAY_ADD_GOLD_ERROR  = 5;
    const PAYMENT_STATUS_NOT_SUCCESS             = 6;
    const PAYMENT_STATUS_CARD_GATEWAY_NOT_ACCEPT = 7;
    const PAYMENT_STATUS_ADVANCE_DEBT_SUCCESS    = 8;
    const PAY_METHOD_ZING_CARD                   = "ZingCard";
    const PAY_METHOD_RECARD                      = "Recard";
    const PAY_METHOD_NAPTHENHANH                 = "NapTheNhanh";
    const PAY_METHOD_BANK_TRANSFER               = "Chuyển khoản";
    const PAY_METHOD_MOMO                        = "MoMo";
    const PAY_METHOD_ADVANCE_DEBT                = "Tạm ứng";
    const PAY_METHOD_ADD_XU_CTV                  = "Add Xu Cho CTV";

    public $fillable = ['amount', 'note', 'payment_type', 'pay_from'];

    protected $table = 'payments';

    public function user()
    {
        $userModelClass = config('t2g_common.models.user_model_class');

        return $this->belongsTo($userModelClass);
    }

    public function creator()
    {
        $userModelClass = config('t2g_common.models.user_model_class');

        return $this->belongsTo($userModelClass, 'creator_id');
    }

    public function getStatusText($isAdmin = true)
    {
        return self::displayStatus($this->status_code, $isAdmin);
    }

    public function info()
    {
        return view('t2g_common::voyager.payments.payment_info', [
            'item'    => $this
        ]);
    }

    public static function displayStatus($statusCode, $isAdmin = false, $withExtraText = true)
    {
        return view(
            't2g_common::voyager.payments.partials.status_text',
            ['isAdmin' => $isAdmin, 'statusCode' => $statusCode, 'withExtraText' => $withExtraText]
        );
    }

    /**
     * @return array
     */
    public static function getPaymentTypes($isAdmin = true)
    {
        $types = [
            self::PAYMENT_TYPE_CARD          => 'Thẻ cào',
            self::PAYMENT_TYPE_MOMO          => 'MoMo',
            self::PAYMENT_TYPE_BANK_TRANSFER => 'Chuyển khoản',
            self::PAYMENT_TYPE_ADVANCE_DEBT  => self::PAY_METHOD_ADVANCE_DEBT,
            self::PAYMENT_TYPE_ADD_XU_CTV    => 'Add Xu Cho CTV',
        ];
        if (!$isAdmin) {
            // display PAYMENT_TYPE_ADD_XU_CTV as PAYMENT_TYPE_BANK_TRANSFER in frontend
            $types[self::PAYMENT_TYPE_ADD_XU_CTV] = 'Chuyển khoản';
        }

        return $types;
    }

    /**
     * @return array
     */
    public static function getPayMethods()
    {
        return [
            self::PAY_METHOD_RECARD,
            self::PAY_METHOD_NAPTHENHANH,
            self::PAY_METHOD_MOMO,
            self::PAY_METHOD_BANK_TRANSFER,
            self::PAY_METHOD_ZING_CARD,
            self::PAY_METHOD_ADD_XU_CTV,
            self::PAY_METHOD_ADVANCE_DEBT,
        ];
    }

    /**
     * @return array
     */
    public static function getStatusCodes()
    {
        return [
            self::PAYMENT_STATUS_SUCCESS,
            self::PAYMENT_STATUS_PROCESSING,
            self::PAYMENT_STATUS_MANUAL_ADD_GOLD_ERROR,
            self::PAYMENT_STATUS_GATEWAY_RESPONSE_ERROR,
            self::PAYMENT_STATUS_GATEWAY_ADD_GOLD_ERROR,
            self::PAYMENT_STATUS_NOT_SUCCESS,
            self::PAYMENT_STATUS_CARD_GATEWAY_NOT_ACCEPT,
            self::PAYMENT_STATUS_ADVANCE_DEBT_SUCCESS,
        ];
    }

    /**
     * @param Payment $payment
     *
     * @return int
     */
    public static function getPaymentStatus(Payment $payment)
    {
        if ($payment->status && $payment->finished) {
            return self::PAYMENT_STATUS_SUCCESS; // thành công
        } else {
            if (!$payment->finished) {
                if ($payment->payment_type != self::PAYMENT_TYPE_CARD) {
                    if (!$payment->gold_added) {
                        // lỗi API nạp tiền
                        return self::PAYMENT_STATUS_MANUAL_ADD_GOLD_ERROR;
                    }
                    if ($payment->payment_type == self::PAYMENT_TYPE_ADVANCE_DEBT) {
                        return self::PAYMENT_STATUS_ADVANCE_DEBT_SUCCESS;
                    }
                } else {
                    if (empty($payment->transaction_id)) {
                        return self::PAYMENT_STATUS_CARD_GATEWAY_NOT_ACCEPT;
                    }
                    return self::PAYMENT_STATUS_PROCESSING; // đang xử lý
                }
            } else {
                if($payment->gateway_status === 2) {
                    return self::PAYMENT_STATUS_GATEWAY_RESPONSE_ERROR; // Recard trả về lỗi
                }
                if($payment->gateway_status === 1 && !$payment->gold_added) {
                    return self::PAYMENT_STATUS_GATEWAY_ADD_GOLD_ERROR; // Recard trả về OK nhưng không add được vàng cho user
                }
            }
        }

        return self::PAYMENT_STATUS_NOT_SUCCESS;
    }

    /**
     * @param $paymentType
     *
     * @return mixed|string
     */
    public static function displayPaymentType($paymentType, $isAdmin = true)
    {
        $types = self::getPaymentTypes($isAdmin);

        return $types[$paymentType] ?? "Unknown";
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    public static function isAcceptable(Payment $payment)
    {
        $status = self::getPaymentStatus($payment);

        return $payment->payment_type
            && (
                $status == self::PAYMENT_STATUS_PROCESSING
                || $status == self::PAYMENT_STATUS_MANUAL_ADD_GOLD_ERROR
                || $status == self::PAYMENT_STATUS_GATEWAY_ADD_GOLD_ERROR
            );
    }

    /**
     * @param Payment $payment
     *
     * @return bool
     */
    public static function isRejectable(Payment $payment)
    {
        return self::isAcceptable($payment);
    }

    public function isDone()
    {
        return self::getPaymentStatus($this) == self::PAYMENT_STATUS_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isInDebt()
    {
        return self::getPaymentStatus($this) == self::PAYMENT_STATUS_ADVANCE_DEBT_SUCCESS;
    }

    /**
     * @param     $payMethod
     * @param int $money
     *
     * @return float|int
     */
    public static function calculateRevenue($payMethod, int $money)
    {
        switch ($payMethod) {
            case Payment::PAY_METHOD_RECARD:
                return $money * ( 100 - config('t2g_common.payment.revenue_rate.recard', 32)) / 100;
            case Payment::PAY_METHOD_NAPTHENHANH:
                return $money * ( 100 - config('t2g_common.payment.revenue_rate.napthenhanh', 31)) / 100;
            case Payment::PAY_METHOD_ZING_CARD:
                return $money * ( 100 - config('t2g_common.payment.revenue_rate.zing', 30)) / 100;
            case Payment::PAY_METHOD_BANK_TRANSFER:
            case Payment::PAYMENT_TYPE_MOMO:
                return $money;
        }

        return $money;
    }

    /**
     * @param $payMethod
     *
     * @return float|1
     */
    public static function getProfitRate($payMethod)
    {
        switch ($payMethod) {
            case Payment::PAY_METHOD_RECARD:
                return ( 100 - config('t2g_common.payment.revenue_rate.recard')) / 100;
            case Payment::PAY_METHOD_NAPTHENHANH:
                return ( 100 - config('t2g_common.payment.revenue_rate.napthenhanh')) / 100;
            case Payment::PAY_METHOD_ZING_CARD:
                return ( 100 - config('t2g_common.payment.revenue_rate.zing')) / 100;
            case Payment::PAY_METHOD_BANK_TRANSFER:
            case Payment::PAYMENT_TYPE_MOMO:
                return 1;
        }

        return 1;
    }
}
