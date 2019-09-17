<?php

namespace T2G\Common\Controllers\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Factory;
use T2G\Common\Exceptions\PaymentApiException;
use T2G\Common\Models\AbstractUser;
use T2G\Common\Models\Payment;
use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\JXApiClient;
use T2G\Common\Util\GameApiLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

/**
 * Class PaymentAdminController
 *
 * @package \T2G\Common\Http\Controllers\Admin
 */
class PaymentBreadController extends VoyagerBaseController
{
    const VOYAGER_SLUG = 'payments';

    protected $searchable = [
        'username', 'card_pin', 'card_serial', 'card_type', 'note', 'id', 'created_at', 'payment_type'
    ];

    /** @var \T2G\Common\Models\AbstractUser|null */
    protected $currentUser;

    public function __construct() {
        /** @var \Illuminate\Contracts\Auth\Guard  $guard */
        $guard = app(\Illuminate\Contracts\Auth\Guard::class);
        $this->currentUser = $guard->user();
    }

    public function index(Request $request)
    {
        voyager()->onLoadingView('voyager::payments.browse', function ($view, &$params) {
            $types = Payment::getPaymentTypes();
            unset($types[Payment::PAYMENT_TYPE_CARD]);
            $params['paymentTypes'] = $types;
            $params['payMethods'] = Payment::getPayMethods();
            $params['statuses'] = Payment::getStatusCodes();
        });

        return parent::index($request);
    }

    public function create(Request $request)
    {
        $this->addDataToAddEditView(true);

        return parent::create($request);
    }

    public function edit(Request $request, $id)
    {
        $this->addDataToAddEditView();

        return parent::edit($request, $id);
    }

    public function report(Request $request, PaymentRepository $paymentRepository)
    {
        $fromDate = $request->get('fromDate', date('Y-m-d', strtotime("-1 weeks")));
        $toDate = $request->get('toDate', date('Y-m-d', strtotime('today')));
        $revenue = $paymentRepository->getRevenueChartData($fromDate, $toDate);

        return view('voyager::payments.report', [
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
            'revenue'  => $revenue,
            'todayRevenue' => $paymentRepository->getRevenueByPeriod(date('Y-m-d')),
            'thisMonthRevenue' => $paymentRepository->getRevenueByPeriod(date('Y-m-01')),
        ]);
    }

    /**
     * @param \T2G\Common\Models\Payment               $payment
     * @param \T2G\Common\Services\JXApiClient         $JXApiClient
     * @param \T2G\Common\Repository\PaymentRepository $paymentRepository
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function accept(Payment $payment, JXApiClient $JXApiClient, PaymentRepository $paymentRepository)
    {
        $this->authorize('edit', $payment);
        if (!Payment::isAcceptable($payment)) {
            $error = "Record đã ghi nhận thành công, hành động không được phép.";
            return $this->returnToListWithError($error, $payment->id);
        }

        list($knb, $xu) = $paymentRepository->exchangeGamecoin($payment->amount, $payment->payment_type);
        $addedGoldStatus = $JXApiClient->addGold($payment->username, $knb, $xu);

        if (!$addedGoldStatus) {
            $error = "Lỗi API nạp vàng, chưa add được vàng cho user";
            return $this->returnToListWithError($error, $payment->id);
        }
        $payment->gamecoin = $knb + $xu;
        $paymentRepository->setDone($payment);

        return redirect()
            ->route("voyager." . self::VOYAGER_SLUG . ".index")
            ->with([
                'message'    => "[#{$payment->id}] Cập nhật thành công",
                'alert-type' => 'success',
            ]);
    }

    /**
     * @param \T2G\Common\Models\Payment               $payment
     * @param \T2G\Common\Repository\PaymentRepository $paymentRepository
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function reject(Payment $payment, PaymentRepository $paymentRepository)
    {
        $this->authorize('edit', $payment);
        if (!Payment::isRejectable($payment)) {
            $error = "Hành động không được phép.";
            return $this->returnToListWithError($error, $payment->id);
        }
        $paymentRepository->setFailed($payment);

        return redirect()
            ->route("voyager." . self::VOYAGER_SLUG . ".index")
            ->with([
                'message'    => "[#{$payment->id}] Cập nhật thành công",
                'alert-type' => 'success',
            ]);
    }

    /**
     * @param bool $isAdding
     */
    private function addDataToAddEditView($isAdding = false)
    {
        voyager()->onLoadingView('voyager::payments.edit-add', function ($view, &$params) use ($isAdding) {
            $types = Payment::getPaymentTypes();
            if ($isAdding) {
                unset($types[Payment::PAYMENT_TYPE_CARD]);
            }

            $params['paymentTypes'] = $types;
        });
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = voyager()->model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));
        $rules = [
            'user_id' => 'required|exists:users,id',
            'payment_type' => ['required', Rule::in(array_keys(Payment::getPaymentTypes()))],
            'amount' => 'integer|gte:10000'
        ];
        /** @var Factory $validator */
        $validator = app(Factory::class);
        $validator = $validator->make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        if (!$request->has('_validate')) {
            try {
                $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());
            } catch (PaymentApiException $e) {
                $payment = $e->getPaymentItem();
                $error = $e->getMessage();
                if ($e->getCode() > 0) {
                    $error = "Lỗi API nạp tiền";
                    $this->getGameApiLogger()->notify("Add vàng thất bại cho user `{$payment->username}` " . $e->getMessage(), [
                        'creator' => $this->currentUser->name,
                        'info' => array_only($payment->toArray(), ['id', 'amount', 'note']),
                    ]);
                }

                if ($request->ajax()) {
                    return response()->json(['errors' => ['note' => $error]]);
                } else {
                    return $this->returnToListWithError($error, $payment->id ?? null);
                }
            }

            event(new BreadDataAdded($dataType, $data));

            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $data]);
            }

            return redirect()
                ->route("voyager.{$dataType->slug}.index")
                ->with(
                    [
                        'message'    => "[#{$data->id}] " . __(
                                'voyager::generic.successfully_added_new'
                            ) . " {$dataType->display_name_singular}",
                        'alert-type' => 'success',
                    ]
                );
        }

        return $this->returnToListWithError("Unknown error", $payment->id ?? null);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = voyager()->model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;

        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

        // Check permission
        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            try {
                $this->insertUpdateData($request, $slug, $dataType->editRows, $data);
            } catch (PaymentApiException $e) {
                $payment = $e->getPaymentItem();
                $this->getGameApiLogger()->notify("Add vàng thất bại cho user `{$payment->username}` " . $e->getMessage(), [
                    'creator' => $this->currentUser->name,
                    'info' => array_only($payment->toArray(), ['id', 'amount', 'note']),
                ]);
                return $this->returnToListWithError($request, $payment->id);
            }
            event(new BreadDataUpdated($dataType, $data));

            return redirect()
                ->route("voyager.{$dataType->slug}.index")
                ->with([
                    'message'    => "[#{$data->id}] Cập nhật thành công",
                    'alert-type' => 'success',
                ]);
        }
    }

    /**
     * @param Request $request
     * @param         $slug
     * @param         $rows
     * @param Payment $data
     *
     * @return \T2G\Common\Models\Payment
     * @throws \T2G\Common\Exceptions\PaymentApiException
     */
    public function insertUpdateData($request, $slug, $rows, $data)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = app(PaymentRepository::class);
        if (empty($data->id)) {
            if ($error = $this->isPaymentAdded($request->get('user_id'), $request->get('amount'))) {
                throw new PaymentApiException($error);
            }
            // create new
            $payment = $this->addNewPayment($request);
            $this->preventPaymentDuplicated($payment);

            return $payment;
        } else {
            $fields = !empty($data->status) ?  ['note', 'payment_type'] : ['note', 'amount', 'payment_type'];
            if ($data->payment_type == Payment::PAYMENT_TYPE_BANK_TRANSFER) {
                $fields[] = 'pay_from';
            }
            $currentStatus = Payment::getPaymentStatus($data);
            if (
                $data->payment_type == Payment::PAYMENT_TYPE_ADVANCE_DEBT
                && $request->get('payment_type') != Payment::PAYMENT_TYPE_ADVANCE_DEBT
                && $currentStatus == Payment::PAYMENT_STATUS_ADVANCE_DEBT_SUCCESS
            ) {
                $paymentRepository->payAdvanceDebt($data);
            }
            $input = array_only($request->all(), $fields);
            $data->fill($input);
            if (isset($input['amount'])) {
                list($knb, $xu) = $paymentRepository->exchangeGamecoin($input['amount'], $data->payment_type);
                $data->gamecoin = $knb + $xu;
            }
            $data->save();

            return $data;
        }
    }

    public function history(AbstractUser $user, Request $request)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = app(PaymentRepository::class);
        $histories = $paymentRepository->getUserPaymentHistory($user);

        return view('vendor.voyager.payment.read', [
            'histories' => $histories,
        ]);
    }

    private function sendPaymentNotification(Payment $payment)
    {
        $paymentTypes = Payment::getPaymentTypes();
        $now = date('Y-m-d H:i:s');
        $text = "[". $paymentTypes[$payment->payment_type] ."]";
        if ($payment->payment_type == Payment::PAYMENT_TYPE_BANK_TRANSFER) {
            $text .= "[{$payment->pay_from}]";
        }
        $text .= " `{$payment->creator->name}` add vào tài khoản `{$payment->username}` `{$payment->gamecoin} Xu` vào lúc {$now}.";
        if ($payment->note) {
            $text .= " Ghi chú: {$payment->note}";
        }

        if (env('APP_ENV') != 'prod') {
            $this->getGameApiLogger()->notify($text);
        } else {
            $discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.add_gold'));
            $discord->send($text);
        }

    }

    /**
     * @param string $error
     * @param        $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function returnToListWithError(string $error, $id = null)
    {
        $message = $id ? "[#{$id}] {$error}" : "$error";

        return redirect()
            ->route("voyager." . self::VOYAGER_SLUG . ".index")
            ->with([
                'message'    => $message,
                'alert-type' => 'error',
            ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \T2G\Common\Models\Payment
     * @throws \T2G\Common\Exceptions\PaymentApiException
     */
    private function addNewPayment(Request $request)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = app(PaymentRepository::class);
        $user = t2g_model('user')->findOrFail($request->get('user_id'));
        $extraData = [];
        $paymentType = $request->get('payment_type');
        $amount = $request->get('amount');
        $isSupportFee = $request->get('support_fee');
        $extraData['pay_method'] = Payment::displayPaymentType($paymentType);
        if ($paymentType == Payment::PAYMENT_TYPE_BANK_TRANSFER) {
            $extraData['pay_from'] = $request->get('pay_from');
        }
        if ($note = $request->get('note')) {
            $extraData['note'] = $note;
        }
        if ($isSupportFee) {
            // phí support không add xu cho gamer
            $knb = $soxu = 0;
        } else {
            list($knb, $soxu) = $paymentRepository->exchangeGameCoin($amount, $paymentType);
        }
        $payment = $paymentRepository->createPayment($user, $paymentType, $amount, $soxu, $extraData);
        if ($isSupportFee) {
            // phí support không add xu cho gamer
            $paymentRepository->setDone($payment, true, false);
            return $payment;
        }
        /** @var JXApiClient $jxApi */
        $jxApi = app(JXApiClient::class);
        if ($addGoldStatus = $jxApi->addGold($user->name, $knb, $soxu)) {
            $paymentRepository->updateRecordAddedGold($payment, $addGoldStatus);
            $this->sendPaymentNotification($payment);
        } else {
            $exception = new PaymentApiException($jxApi->getLastResponse(), PaymentApiException::GAME_PAYMENT_API_ERROR_CODE);
            $exception->setPaymentItem($payment);
            throw $exception;
        }

        return $payment;
    }

    private function preventPaymentDuplicated(Payment $payment)
    {
        $key = "ADD_GOLD_LOCKED_{$payment->user_id}_{$payment->amount}";
        $message = sprintf("User %s vừa được add %s Xu bởi %s vào lúc %s. Vui lòng thử lại sau 5 phút", $payment->username, $payment->gamecoin, $payment->creator->name, $payment->created_at->format('H:i'));
        app('cache.store')->set($key, $message, 5);
    }

    private function isPaymentAdded($userId, $amount)
    {
        $key = "ADD_GOLD_LOCKED_{$userId}_{$amount}";

        return app('cache.store')->get($key);
    }

    protected function alterBreadBrowseEloquentQuery(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        if ($keyword = $request->get('keyword')) {
            $query->whereRaw("(card_pin LIKE '%{$keyword}%' OR card_serial LIKE '%{$keyword}%')");
        }
        if ($id = $request->get('id')) {
            $query->where('id', $id);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($payMethod = $request->get('pay_method')) {
            $query->where('pay_method', $payMethod);
        }
        if ($createdAt = trim($request->get('created_at'))) {
            if (strpos($createdAt, '>') === 0 || strpos($createdAt, '<') === 0) {
                $operator = $createdAt[0];
                $createdAt = trim(str_replace($operator, '', $createdAt));
                $query->whereRaw("created_at {$operator} '{$createdAt}'");
            } else {
                $query->where('created_at', 'LIKE', $createdAt . "%");
            }

        }
        if ($cardType = $request->get('card_type')) {
            $query->where('card_type', $cardType);
        }

        if ($cardType = $request->get('pay_from')) {
            $query->where('pay_from', $cardType);
        }

        if ($statusCode = $request->get('status_code')) {
            $query->where('status_code', $statusCode);
        }
    }

    /**
     * @return GameApiLog
     */
    protected function getGameApiLogger()
    {
        return app(GameApiLog::class);
    }
}