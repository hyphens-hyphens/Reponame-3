<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Models\Revision;
use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Repository\UserRepository;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataType;

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

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = $dataType->server_side ? 'paginate' : 'get';

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
        $searchable = $this->getSearchableFields($dataType);
        $orderBy = $request->get('order_by');
        $sortOrder = $request->get('sort_order', null);

        $stringSearch = str_replace(' ', ',', $search->value);
        $arrSearch    = array_filter(explode(',',$stringSearch), function($value) { return !is_null($value) && $value !== ''; });
        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $relationships = $this->getRelationships($dataType);

            $model = app($dataType->model_name);
            $query = $this->getBreadBrowseEloquentQuery($dataType, $model, $search, $orderBy, $sortOrder, $arrSearch);
            $this->alterBreadBrowseEloquentQuery($query, $request);
        } else {
            // If Model doesn't exist, get data from table name
            $model = false;
            $query = $this->getBreadBrowseDbQuery($dataType);
            $this->alterBreadBrowseDbQuery($query, $request);
        }

        $dataTypeContent = call_user_func([$query, $getter]);
        if (strlen($dataType->model_name) != 0) {
            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        }

        // Check if BREAD is Translatable
        if (($isModelTranslatable = is_bread_translatable($model))) {
            $dataTypeContent->load('translations');
        }

        // Check if server side pagination is enabled
        $isServerSide = isset($dataType->server_side) && $dataType->server_side;

        $view = Voyager::getBreadView('browse', $slug);

        return Voyager::view($view, compact(
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'orderBy',
            'sortOrder',
            'searchable',
            'isServerSide'
        ));
    }


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

    /**
     * @param  DataType  $dataType
     * @param $model
     * @param $search
     * @param  null  $orderBy
     * @param  null  $sortOrder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBreadBrowseEloquentQuery(
        DataType $dataType,
        $model,
        $search,
        $orderBy = null,
        $sortOrder = null,
        $arrSearch = null
    ) {
        $relationships = $this->getRelationships($dataType);
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $model::select('*');
        $query->with($relationships);
        // If a column has a relationship associated with    it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'browse');
        if ($search->value && $search->key && $search->filter) {
            if (!empty($arrSearch)) {
                if ($search->filter == 'contains') {
                    $search_filter    = 'LIKE';
                    $firstValueSearch = '%'.array_shift($arrSearch).'%';
                    $query->where($search->key, $search_filter, $firstValueSearch);
                    if (!empty($arrSearch)) {
                        foreach ($arrSearch as $searchValue) {
                            $searchValue = '%'.$searchValue.'%';
                            $query->orWhere($search->key, $search_filter, $searchValue);
                        }
                    }
                }else {
                    $search_filter    = '=';
                    $firstValueSearch = array_shift($arrSearch);
                    $query->where($search->key, $search_filter, $firstValueSearch);
                   if(!empty($arrSearch)) {
                       foreach ($arrSearch as $searchValue) {
                           $query->orWhere($search->key, $search_filter, $searchValue);
                       }
                   }
                }
            }
        }

        if ($orderBy && in_array($orderBy, $dataType->fields())) {
            $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'DESC';
            $query->orderBy($orderBy, $querySortOrder);
        } elseif ($model->timestamps) {
            $query->latest($model::CREATED_AT);
        } else {
            $query->orderBy($model->getKeyName(), 'DESC');
        }

        return $query;
    }
}
