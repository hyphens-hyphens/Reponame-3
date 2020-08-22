<?php

namespace T2G\Common\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use T2G\Common\Models\GiftCode;
use T2G\Common\Repository\GiftCodeItemRepository;
use T2G\Common\Repository\GiftCodeRepository;
use Illuminate\Http\Request;
use T2G\Common\Services\GiftCodeService;

/**
 * Class UserBreadController
 *
 * @package \T2G\Common\Http\Controllers\Admin
 */
class GiftCodeBreadController extends BaseVoyagerController
{
    protected $searchable = [
        'prefix', 'type'
    ];

    public function store(Request $request)
    {
        $response = parent::store($request);
        if ($response instanceof RedirectResponse) {
            $created = GiftCode::latest('id')->first();
            $response->setTargetUrl(route('voyager.gift-codes.edit', [$created->id]));

            return $response;
        }

        return $response;
    }

    public function update(Request $request, $id)
    {
        $response = parent::update($request, $id);
        if ($response instanceof RedirectResponse) {
            $response->setTargetUrl(route('voyager.gift-codes.edit', [$id]));

            return $response;
        }

        return $response;
    }

    public function edit(Request $request, $id)
    {
        voyager()->onLoadingView('voyager::gift-codes.edit-add', function ($view, &$params) use ($id, $request) {
            $giftCode = GiftCode::find($id);
            $params['codes'] = app(GiftCodeItemRepository::class)->getCodeItems($giftCode, $request);
        });

        return parent::edit($request, $id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param array                    $data
     * @param null                     $name
     * @param null                     $id
     *
     * @return \Illuminate\Http\Request|\Illuminate\Validation\Validator|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function validateBread($request, $data, $name = null, $id = null)
    {
        $uniquePrefixRule = Rule::unique('gift_codes');
        if ($id) {
            $uniquePrefixRule->ignore($id);
        }
        $rules = [
            'prefix'   => [
                'required',
                'between:2,10',
                $uniquePrefixRule
            ],
            'type'     => [
                'required', Rule::in(array_keys(GiftCodeRepository::getTypes()))
            ],
            'quantity' => 'min:0|max:10000',
        ];

        return app(\Illuminate\Validation\Factory::class)->make($request, $rules);
    }

    /**
     * @param Request $request
     * @param         $slug
     * @param         $rows
     * @param         $data
     *
     * @return \T2G\Common\Models\GiftCode
     */
    public function insertUpdateData($request, $slug, $rows, $data)
    {
        /** @var \T2G\Common\Models\GiftCode $giftCode */
        $giftCode = $data;
        $giftCode->fill(array_only($request->all(), ['name', 'prefix', 'type', 'expired_at', 'code_name']));
        $giftCode->save();
        if ($giftCode->type == GiftCode::TYPE_PER_ACCOUNT && $request->get('quantity')) {
            app(GiftCodeService::class)->generateCode($giftCode, $request->get('quantity'));
        }

        return $giftCode;
    }

    protected function alterBreadBrowseEloquentQuery(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        $query->with('details');
    }
}
