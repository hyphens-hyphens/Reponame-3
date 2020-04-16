<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Models\Revision;
use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Repository\UserRepository;
use Illuminate\Http\Request;

/**
 * Class UserBreadController
 *
 * @package \T2G\Common\Http\Controllers\Admin
 */
class UserBreadController extends BaseVoyagerController
{
    protected $searchable = [
        'name', 'phone', 'note', 'id', 'email'
    ];

    public function edit(Request $request, $id)
    {
        voyager()->onLoadingView('voyager::users.edit-add', function ($view, &$params) {
            /** @var PaymentRepository $paymentRepository */
            $paymentRepository = app(PaymentRepository::class);
            if (!empty($params['dataTypeContent'])) {
                $params['histories'] = $paymentRepository->getUserPaymentHistory($params['dataTypeContent']);
            }
        });

        return parent::edit($request, $id);
    }

    public function validateBread($request, $data, $name = null, $id = null)
    {
        $rules = [];
        if (!empty($request['password'])) {
            $rules['password'] = 'between:6,32';
        }

        if (!empty($request['password2'])) {
            $rules['password2'] = 'between:6,32';
        }

        return app(\Illuminate\Validation\Factory::class)->make($request, $rules);
    }

    /**
     * @param Request $request
     * @param $slug
     * @param $rows
     * @param $data
     *
     * @return \T2G\Common\Models\AbstractUser
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function insertUpdateData($request, $slug, $rows, $data)
    {
        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = $data;
        $user->fill(array_only($request->all(), ['name', 'avatar', 'phone', 'email', 'role_id', 'note']));
        if ($password = $request->get('password')) {
            $this->authorize('editPassword', $data);
            $userRepository->updatePassword($user, $password);
        }
        if ($password2 = $request->get('password2')) {
            $this->authorize('editPassword', $data);
            $userRepository->updatePassword2($user, $password2);
        }
        if (!$password && !$password2) {
            $user->save();
        }

        return $user;
    }

    protected function alterBreadBrowseEloquentQuery(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        $query->with(['payments', 'roles', 'role']);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function quickEdit(Request $request)
    {
        $slug = $this->getSlug($request);
        $field = $request->input('name');
        $value = $request->input('value');
        $id = $request->input('pk');
        voyager()->model('DataType')->where('slug', '=', $slug)->firstOrFail();
        if (!in_array($field, $this->getEditableFields())) {
            return response()->json(['errors' => ["You are not allowed to perform this action on field `{$field}``"]]);
        }
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = t2g_model('user')->findOrFail($id);
        // Check permission
        $this->authorize('edit', $user);
        $userRepository = app(UserRepository::class);

        if ($field == 'password' || $field == 'password2') {
            $this->authorize('editPassword', $user);
            if (!$value) {
                $value = substr(md5(time()), 0, 10);
            }
            if ($field == 'password') {
                $userRepository->updatePassword($user, $value);
            } else {
                $userRepository->updatePassword2($user, $value);
            }
        } else {
            $user->{$field} = $value;
            $user->save();
        }

        return response()->json(['success' => true, 'newValue' => $value]);
    }

    protected function getEditableFields()
    {
        return ['password', 'password2', 'phone', 'note'];
    }

    public function report()
    {
        $fromDate = request('fromDate', date('Y-m-d', strtotime("-2 weeks")));
        $toDate = request('toDate', date('Y-m-d', strtotime('today')));
        $data = [
            'fromDate'        => $fromDate,
            'toDate'          => $toDate,
            'registeredChart' => $this->getUsersReportData($fromDate, $toDate)
        ];

        return voyager()->view('t2g_common::voyager.users.report', $data);
    }

    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return array
     */
    protected function getUsersReportData($fromDate, $toDate)
    {
        $nru = 0;
        $userRepository = app(UserRepository::class);
        list($reportData, $campaigns) = $userRepository->getUserRegisteredReport($fromDate, $toDate);
        // prepare chart data
        $yAxisData = [
            'direct' => [],
            'mkt'    => [],
        ];
        $dateArray = [];
        foreach ($reportData as $date => $reportDatum) {
            $dateArray[] = $date;
            $direct = 0;
            $mkt = 0;
            foreach ($reportDatum['details'] as $cid => $total) {
                $nru += $total;
                if ($cid == 'not-set|not-set|not-set') {
                    $direct += $total;
                } else {
                    $mkt += $total;
                }
            }
            $yAxisData['direct'][] = $direct;
            $yAxisData['mkt'][] = $mkt;
        }
        $data = [
            'dateArray'                  => $dateArray,
            'fromDate'                   => $fromDate,
            'toDate'                     => $toDate,
            'reportRegisteredByCampaign' => [
                'data'      => $reportData,
                'campaigns' => $campaigns,
            ],
            'chart'                      => [
                'xAxisData' => $dateArray,
                'yAxisData' => $yAxisData
            ],
            'nru' => $nru
        ];

        return $data;
    }

    public function revertRevision(Request $request, UserRepository $userRepository)
    {
        $revisionId = $request->input('revision_id');
        /** @var Revision $revision */
        $revision = Revision::findOrFail($revisionId);
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = $revision->historyOf();
        // Check permission
        $this->authorize('revert', $user);
        $field = $revision->key;
        if ($field == 'raw_password' || $field == 'password2') {
            $this->authorize('editPassword', $user);
            if ($field == 'raw_password') {
                $userRepository->updatePassword($user, $revision->oldValue());
            } else {
                $userRepository->updatePassword2($user, $revision->oldValue());
            }
        } else {
            $user->{$field} = $revision->oldValue();
            $user->save();
        }

        return redirect()
            ->route("voyager.users.edit", [$user->id])
            ->with([
                'message'    => 'Phục hồi thành công. User #' . $user->name,
                'alert-type' => 'success',
            ]);
    }
}
