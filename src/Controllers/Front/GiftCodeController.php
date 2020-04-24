<?php

namespace T2G\Common\Controllers\Front;

use T2G\Common\Repository\GiftCodeRepository;

/**
 * Class GiftCodeController
 *
 * @package \App\Http\Controllers\Front
 */
class GiftCodeController extends BaseFrontController
{
    /**
     * @param \T2G\Common\Repository\GiftCodeRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWelcomeCode(GiftCodeRepository $repository)
    {
        $data = [];
        $user = \Auth::user();
        $giftCode = $repository->getCodeOwnedByUser($user);
        if (!$giftCode) {
            $giftCode = $repository->getWelcomeCode();
            if (!$giftCode) {
                $data['message'] = "Code Tân Thủ đã hết, vui lòng liên hệ fanpage để được hỗ trợ.";
            } else {
                $repository->issueCodeForUser($user, $giftCode);
            }
        }
        if ($giftCode) {
            $data['code'] = $giftCode->code;
        }

        return response()->json($data);
    }
}
