<?php

namespace T2G\Common\Repository;

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
            ->where('created_at', '>', new \DateTime("-5 minutes"))
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
     * @param \T2G\Common\Models\AbstractUser $user
     * @param \T2G\Common\Util\MobileCard     $card
     * @param                                 $gameCoin
     * @param                                 $payMethod
     *
     * @return \T2G\Common\Models\Payment
     */
    public function createCardPayment(AbstractUser $user, MobileCard $card, $gameCoin, $payMethod)
    {
        $data = [
            'card_type'   => $card->getType(),
            'card_serial' => $card->getSerial(),
            'card_pin'    => $card->getCode(),
            'card_amount' => $card->getAmount(),
            'pay_method'  => $payMethod,
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
        if ($record->payment_type != Payment::PAYMENT_TYPE_ADVANCE_DEBT) {
            $record->finished   = true;
        }
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
        $query->where('user_id', $user->id)
            ->orderBy('created_at', 'DESC');

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

    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return array
     */
    public function getRevenueChartData($fromDate, $toDate){
        $count = CommonHelper::subDate($fromDate, $toDate);
        $results = $this->db->table($this->model->getTable())
            ->selectRaw("
            DATE_FORMAT(`created_at`, '%d-%m') AS `date`, `pay_method`, SUM(`amount`)/1000 as `total`,
            SUM(`profit`)/1000 as `total_profit`
            ")
            ->whereRaw("`created_at` BETWEEN '{$fromDate} 00:00:00' AND '{$toDate} 23:59:59' AND `status_code` = 1")
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
                    $data[$day][$value->pay_method]['total'] = $value->total;
                    $data[$day][$value->pay_method]['profit'] = $value->total_profit;
                }
            }
        }
        $series = $seriesData = [];
        $total = $totalRevenue = 0;
        foreach ($data as $key => $val) {
            $series[] = "'$key'";
            foreach ($payMethods as $payMethod) {
                $payByDay = isset($val[$payMethod]['total']) ? $val[$payMethod]['total'] : 0;
                $seriesData[$payMethod][] = $payByDay;
                $total += $payByDay;
                $totalRevenue += isset($val[$payMethod]['profit']) ? $val[$payMethod]['profit'] : 0;
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
        $result = $query->selectRaw("SUM(amount) as total, SUM(profit) as profit, status_code")
            ->where('status_code', 1)
            ->groupBy('status_code')
            ->first()
        ;

        return $result ? $result->toArray() : ['total' => 0, 'profit' => 0];
    }

    /**
     * @param \T2G\Common\Models\Payment $payment
     */
    public function payAdvanceDebt(Payment $payment)
    {
        $payment->status = true;
        $payment->finished = true;
    }

    /**
     * @return array
     */
    public function getRevenueChartForWidget()
    {
        $fromDate = date('Y-m-d 00:00:00', strtotime("-10 days"));
        $toDate = date('Y-m-d 23:59:59', strtotime("-1 day"));
        $results = $this->db->table($this->model->getTable())
            ->selectRaw("
            DATE_FORMAT(`created_at`, '%d-%m') AS `date`, 
            SUM(`amount`) as `total`,
            DATE_FORMAT(created_at, '%m-%d') as `ordered_date`
            ")
            ->whereRaw("`created_at` BETWEEN '{$fromDate}' AND '{$toDate}' AND `status_code` = 1")
            ->groupBy('date', 'ordered_date')
            ->orderBy('ordered_date', 'ASC')
            ->get()
        ;

        return $results;
    }

    public function getPayUsers($fromDate, $toDate)
    {
        $query = $this->query();
        $query
            ->selectRaw("count(DISTINCT(`user_id`)) as pay_users")
            ->where('status_code', 1)
            ->whereBetween('created_at', [$fromDate, $toDate])
        ;
        $count = $query->first()->toArray();

        return $count['pay_users'] ?? 0;
    }

    /**
     * @param $username
     *
     * @return int
     */
    public function isUserPaid($username)
    {
        $query = $this->query();
        $query->where('username', $username)
            ->where('status_code', 1)
        ;

        return $query->count();
    }

}
