<?php

namespace T2G\Common\Controllers\Admin;

use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Repository\UserRepository;
use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

/**
 * Class UserBreadController
 *
 * @package \T2G\Common\Http\Controllers\Admin
 */
class UserBreadController extends VoyagerBaseController
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

        return app(\Illuminate\Validation\Validator::class)->make($request, $rules);
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
        $user->fill(array_only($request->all(), ['name', 'phone', 'email', 'role_id', 'note']));
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
        $query->with(['payments', 'roles']);
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
        $dataType = voyager()->model('DataType')->where('slug', '=', $slug)->firstOrFail();
        if (!in_array($field, $this->getEditableFields())) {
            return response()->json(['errors' => ["You are not allowed to perform this action on field `{$field}``"]]);
        }
        // Check permission
        $this->authorize('edit', app($dataType->model_name));
        $userRepository = app(UserRepository::class);
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = t2g_model('user')->findOrFail($id);
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
}