<?php

namespace T2G\Common\Util;

use T2G\Common\Models\Payment;

/**
 * Class CommonHelper
 *
 */
class CommonHelper
{
    /**
     * @param        $date
     * @param string $format
     *
     * @return string
     * @throws \Exception
     */
    public static function formatDate($date, $format = 'd-m')
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }

        return $date->format($format);
    }

    /**
     * @param string $href
     * @param string $quote
     * @param string $hashTag
     * @param string $redirectUri
     * @param string $display
     *
     * @return string
     */
    public static function getFbShareUrl($href = '', $quote = '', $hashTag = '', $redirectUri = '', $display = 'popup')
    {
        $href = $href ? $href : url()->current();
        $params = [
            'app_id'       => config('site.fb.app_id'),
            'display'      => $display,
            'href'         => $href,
            'redirect_uri' => $redirectUri,
            'quote'        => $quote,
            'hashtag'      => $hashTag,
        ];

        return "https://www.facebook.com/dialog/share?" . http_build_query($params);
    }

    /**
     * @param $date1 Y-m-d
     * @param $date2 Y-m-d
     *
     * @return float
     */
    public static function subDate($date1, $date2) {
        $first_date = strtotime($date1);
        $second_date = strtotime($date2);
        $dateDiff = abs($first_date - $second_date);

        return floor($dateDiff / (60 * 60 * 24));
    }

    public static function getIconForPaymentType($paymentType)
    {
        $icons = [
            Payment::PAYMENT_TYPE_CARD          => 'voyager-credit-card',
            Payment::PAYMENT_TYPE_MOMO          => 'voyager-wallet',
            Payment::PAYMENT_TYPE_BANK_TRANSFER => 'voyager-receipt',
        ];

        return $icons[$paymentType] ?? 'voyager-exclamation';
    }

    /**
     * @param $rawHwid
     *
     * @return string
     */
    public static function getFilteredHwid($rawHwid)
    {
        $hwidPieces = explode('-', $rawHwid);
        $newHwidArray = ['X', 'X', 'X', $hwidPieces[3], $hwidPieces[4], 'X', $hwidPieces[6], 'X'];

        return implode('-', $newHwidArray);
    }
}
