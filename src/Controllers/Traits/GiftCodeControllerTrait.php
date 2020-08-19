<?php
namespace T2G\Common\Controllers\Traits;

use T2G\Common\Exceptions\GiftCodeException;
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
            $added = $giftCodeService->useCode($user, $giftCodeItem);
            if (!$added) {
                $error = trans("gift_code.race_condition_error");
            }
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
}
