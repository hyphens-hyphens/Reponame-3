<?php

namespace T2G\Common\Repository;

use Illuminate\Database\DatabaseManager;
use T2G\Common\Models\Payment;
use T2G\Common\Models\AbstractUser;
use T2G\Common\Util\CommonHelper;
use T2G\Common\Util\MobileCard;

/**
 * Class PaymentRepository
 *
 * @package \T2G\Common\Repository
 */
class PaymentRepository extends AbstractEloquentRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return config('t2g_common.models.payment_model_class');
    }

    /**
     * @param \T2G\Common\Util\MobileCard $card
     *
     * @return int
     * @throws \Exception
     */
    public function isCardExisted(MobileCard $card)
    {
        $query = $this->query();
        $query->where('card_pin', 'LIKE', $card->getCode())
            ->where('card_serial', 'LIKE', $card->getSerial())
            ->where('card_type', $card->getType())
            ->where('created_at', '>', new \DateTime("-30 minutes"))
        ;

        return $query->count();
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     * @param                      $transactionCode
     *
     * @return \T2G\Common\Models\Payment
     */
    public function updateCardPayment(Payment $payment, $transactionCode)
    {
        $payment->transaction_id = $transactionCode;
        $payment->save();

        return $payment;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser            $user
     * @param \T2G\Common\Util\MobileCard $card
     * @param                      $gameCoin
     *
     * @return \T2G\Common\Models\Payment
     */
    public function createCardPayment(AbstractUser $user, MobileCard $card, $gameCoin)
    {
        $data = [
            'card_type'   => $card->getType(),
            'card_serial' => $card->getSerial(),
            'card_pin'    => $card->getCode(),
            'card_amount' => $card->getAmount(),
        ];
        $payment = $this->createPayment($user, Payment::PAYMENT_TYPE_CARD, $card->getAmount(), $gameCoin, $data);

        return $payment;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $type
     * @param                                 $amount
     * @param                                 $gamecoin
     * @param                                 $extraData
     *
     * @return Payment
     */
    public function createPayment(AbstractUser $user, $type, $amount, $gamecoin, $extraData)
    {
        /** @var \Illuminate\Contracts\Auth\Guard $guard */
        $guard = app('auth.driver');
        /** @var AbstractUser $currentUser */
        $currentUser = $guard->user();
        $payment = t2g_model('payment');
        $data = [
            'user_id'      => $user->id,
            'username'     => $user->name,
            'amount'       => $amount,
            'gamecoin'     => $gamecoin,
            'ip'           => request()->getClientIp(),
            'utm_medium'   => $user->utm_medium,
            'utm_source'   => $user->utm_source,
            'utm_campaign' => $user->utm_campaign,
            'creator_id'   => $currentUser->id,
        ];
        $data = array_merge($data, $extraData);
        foreach ($data as $attribute => $value) {
            $payment->{$attribute} = $value;
        }
        // set payment_type at last because we are using a Mutator that depend on others attributes
        $payment->payment_type = $type;
        $payment->save();

        return $payment;
    }

    /**
     * @param $transactionCode
     *
     * @return Payment|null
     */
    public function getByTransactionCode($transactionCode)
    {
        $query = $this->query();
        $query->where('transaction_id', $transactionCode);
        /** @var Payment $payment */
        $payment = $query->first();

        return $payment;
    }

    /**
     * @param \T2G\Common\Models\Payment $record
     * @param                            $status
     * @param                            $reason
     * @param                            $amount
     */
    public function updateCardPaymentTransaction(Payment $record, $status, $reason, $amount)
    {
        $record->gateway_status = $status;
        if (!$status) {
            $record->gateway_response = $reason;
        }
        $record->gateway_amount = $amount;
        $record->finished = true;
        $record->save();
    }

    /**
     * @param \T2G\Common\Models\Payment $record
     * @param                            $status
     */
    public function updateRecordAddedGold(Payment $record, $status)
    {
        $record->gold_added = $status;
        $record->status     = $status;
        $record->finished   = true;
        $record->save();
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function makeUserPaymentHistoryQuery(AbstractUser $user)
    {
        $query = $this->query();
        $query->where('user_id', $user->id);

        return $query;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param int       $limit
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserPaymentHistory(AbstractUser $user, $limit = 10)
    {
        $query = $this->makeUserPaymentHistoryQuery($user);

        return $query->paginate($limit);
    }

    /**
     * @param $moneyAmount
     * @param $paymentType
     *
     * @return array
     */
    public function exchangeGamecoin($moneyAmount, $paymentType)
    {
        $knb = $xu = 0;
        $gameCoinAmount = round($moneyAmount / config('t2g_common.payment.game_gold.exchange_rate', 1000));
        if (in_array($paymentType, [Payment::PAYMENT_TYPE_BANK_TRANSFER, Payment::PAYMENT_TYPE_MOMO, Payment::PAYMENT_TYPE_ADVANCE_DEBT])) {
            $xu = $gameCoinAmount + ceil($gameCoinAmount * config('t2g_common.payment.game_gold.bonus_rate', 10) / 100);
        } else {
            $knb = $gameCoinAmount;
        }

        return [$knb, $xu];
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     * @param bool                       $status
     * @param bool                       $goldAdded
     *
     * @return \T2G\Common\Models\Payment
     */
    public function setDone(Payment $payment, $status = true, $goldAdded = true)
    {
        $payment->finished = true;
        $payment->status = $status;
        $payment->gold_added = $goldAdded;
        $payment->save();

        return $payment;
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     *
     * @return \T2G\Common\Models\Payment
     */
    public function setFailed(Payment $payment)
    {
        $payment->finished = true;
        $payment->status = false;
        $payment->gold_added = false;
        $payment->note = "Cập nhật bởi Moderator";
        $payment->save();

        return $payment;
    }

    function getRevenueChartData($fromDate, $toDate){
        $count = CommonHelper::subDate($fromDate, $toDate);
        /** @var DatabaseManager $db */
        $db = app(DatabaseManager::class);
        $results = $db->table('payments')->selectRaw("DATE_FORMAT(`created_at`, '%d-%m') AS `date`, `pay_method`, SUM(`amount`)/1000 as `total`")
            ->whereRaw("`created_at` BETWEEN '{$fromDate} 00:00:00' AND '{$toDate} 23:59:59' AND `status` = 1")
            ->groupBy('pay_method', 'date')
            ->orderBy('date', 'ASC')
            ->get()
        ;
        //order data
        $data = $payMethods = [];
        for($i = 0; $i <= $count; $i ++){
            $startOfDay = mktime(0, 0, 0, date('n', strtotime($fromDate)), date('d', strtotime($fromDate)) + $i);
            $day = date('d-m', $startOfDay);
            foreach ($results as $key => $value) {
                if (!in_array($value->pay_method, $payMethods)) {
                    $payMethods[] = $value->pay_method;
                }
                if ($value->date == $day) {
                    $data[$day][$value->pay_method] = $value->total;
                }
            }
        }
        $series = $seriesData = [];
        $total = $totalRevenue = 0;
        foreach ($data as $key => $val) {
            $series[] = "'$key'";
            foreach ($payMethods as $payMethod) {
                $payByDay = isset($val[$payMethod]) ? $val[$payMethod] : 0;
                $seriesData[$payMethod][] = $payByDay;
                $total += $payByDay;
                $totalRevenue += $this->calculateRevenue($payMethod, $payByDay);
            }
        }

        return [
            'series'       => implode(',', $series),
            'seriesData'   => $seriesData,
            'total'        => $total,
            'totalRevenue' => $totalRevenue,
        ];
    }

    /**
     * @param      $fromDate
     * @param null $toDate
     *
     * @return array []
     */
    public function getRevenueByPeriod($fromDate = null, $toDate = null)
    {
        $query = $this->query();
        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }
        $results = $query->selectRaw("SUM(amount) as total, pay_method")
            ->where('status', 1)
            ->groupBy('pay_method')
            ->get()
        ;
        $revenue = [
            'total'   => 0,
            'revenue' => 0,
        ];
        foreach ($results as $result) {
            $revenue['total'] += $result->total;
            $revenue['revenue'] += $this->calculateRevenue($result->pay_method, $result->total);
        }

        return $revenue;
    }

    /**
     * @param     $name
     * @param int $money
     *
     * @return float|int
     */
    private function calculateRevenue($name, int $money)
    {
        switch ($name) {
            case Payment::PAY_METHOD_RECARD:
                return $money * ( 100 - config('t2g_common.payment.revenue_rate.recard', 32)) / 100;
            case Payment::PAY_METHOD_NAPTHENHANH:
                return $money * ( 100 - config('t2g_common.payment.revenue_rate.napthenhanh', 28)) / 100;
            case Payment::PAY_METHOD_ZING_CARD:
                return $money * ( 100 - config('t2g_common.payment.revenue_rate.zing', 30)) / 100;
            case Payment::PAYMENT_TYPE_BANK_TRANSFER:
            case Payment::PAYMENT_TYPE_MOMO:
                return $money;
        }

        return $money;
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    public function payAdvanceDebt(Payment $payment)
    {
        $payment->status = true;
        $payment->finished = true;
    }
}
