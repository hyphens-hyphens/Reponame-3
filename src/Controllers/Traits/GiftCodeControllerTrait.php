<?php
namespace T2G\Common\Controllers\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use T2G\Common\Exceptions\GiftCodeException;
use T2G\Common\Models\GiftCode;
use T2G\Common\Requests\UseCodeRequest;
use T2G\Common\Services\GiftCodeService;

/**
 * Class GiftCodeController
 *
 * @package \App\Http\Controllers\Front
 */
trait GiftCodeControllerTrait
{
    /**
     * @param \T2G\Common\Requests\UseCodeRequest  $request
     * @param \T2G\Common\Services\GiftCodeService $giftCodeService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function useCode(UseCodeRequest $request, GiftCodeService $giftCodeService)
    {
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = \Auth::user();
        if (!$user) {
            return back()->withErrors(['code' => trans("gift_code.login_first")]);
        }
        $data = $request->validated();
        $error = '';
        try {
            $giftCodeItem = $giftCodeService->getGiftCodeItem($data['code']);
            $giftCodeService->useCode($user, $giftCodeItem);
        } catch (GiftCodeException $e) {
            $error = trans("gift_code." . $e->getMessage());
            if ($e->getCode() == GiftCodeException::ERROR_CODE_API_ERROR) {
                \Log::critical("Cannot add code for user `{$user->name}`", $e->getGiftCode()->toArray());
            }
        }
        if ($error) {
            return back()->withErrors(['code' => $error]);
        }

        return back()->with('status', trans("gift_code.used_code_successfully"));
    }

    /**
     * @param \Illuminate\Http\Request             $request
     * @param \T2G\Common\Services\GiftCodeService $giftCodeService
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function claimCode(Request $request, GiftCodeService $giftCodeService)
    {
        $status = false;
        $user = \Auth::user();
        if (!$user) {
            return back()->with(['claim_message' => trans("gift_code.login_first"), 'claim_status' => $status]);
        }
        $giftCodeId = $request->get('gift_code_id');
        $giftCode = GiftCode::find($giftCodeId);
        try {
            if (!$giftCode || !$giftCode->is_claimable || $giftCode->isUserClaimed($user)) {
                throw new BadRequestException();
            }
            $giftCodeService->claimCode($user, $giftCode);
            $status = true;
            $message = trans('gift_code.claimed_successful');
        } catch (GiftCodeException $e) {
            \Log::critical($e->getMessage(), [
                'code' => $giftCode->toArray(),
                'user' => array_only($user->toArray(), ['id', 'name'])
            ]);
            $message = trans('gift_code.internal_error');
        } catch (BadRequestException $e) {
            $message = trans('gift_code.bad_claim_request');
        }

        return back()->with(['claim_message' => $message, 'claim_status' => $status]);
    }
}
