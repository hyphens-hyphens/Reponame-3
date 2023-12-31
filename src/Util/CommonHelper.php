<?php

namespace T2G\Common\Util;

use Illuminate\Support\Str;
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
            Payment::PAYMENT_TYPE_CARD                => 'voyager-credit-card',
            Payment::PAYMENT_TYPE_MOMO                => 'voyager-wallet',
            Payment::PAYMENT_TYPE_BANK_TRANSFER       => 'voyager-receipt',
            Payment::PAYMENT_TYPE_ADD_XU_CTV          => 'voyager-gift',
            Payment::PAYMENT_TYPE_TRAO_THUONG_XU      => 'voyager-wallet',
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
        if (count($hwidPieces) < 8) {
            return str_repeat('X-', 7) . "X";
        }
        $newHwidArray = ['X', $hwidPieces[1], $hwidPieces[2], $hwidPieces[3], $hwidPieces[4], $hwidPieces[5], 'X', 'X'];

        return implode('-', $newHwidArray);
    }

    public static function displayHwid($hwid)
    {
        return '<span>' . str_replace('-','</span><span class="hwid-divider">-</span><span>', $hwid) . '</span>';
    }

    /**
     * @param string|null $phone
     *
     * @return string
     */
    public static function cleanPhoneValue(?string $phone)
    {
        return trim(str_replace([' ', '-', '.'], ['', '', ''], $phone));
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function makeKeyword($text)
    {
        return Str::slug($text, ' ');
    }
}
