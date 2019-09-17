<?php

namespace T2G\Common\Tests\Unit;

use PHPUnit\Framework\TestCase;
use T2G\Common\Services\SmsNotifierParser;

class SmsNotifierParserTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @param $message
     * @param $expected
     *
     * @return void
     * @dataProvider skippedMessageProvider
     */
    public function testIsSkippedMessage($message, $expected)
    {
        $instance = new SmsNotifierParser();
        $this->assertEquals($expected, $instance->isSkippedMessage($message));
    }

    public function skippedMessageProvider()
    {
        return [
            [
                'DongA Bank thong bao: TK 0110666501 da thay doi: +200,000 VND. Ecom.EW19080158408886.MOMO.0763998413.CashOut. So du hien tai la:...',
                true
            ],
            [
                'SD TK 0071001400512 +200,000VND luc 19-06-2019 20:50:40. SD 83,157,241VND. Ref Ecom.EW19080158408886.MOMO.0763998413.CashOut',
                true
            ],
            [
                'DongA Bank thong bao: TK 0110666501 da thay doi: +200,000 VND. 863712.010819.091441.ntn 6890 FT19213990841750. So du hien tai la:...',
                true
            ],
            [
                'SD TK 0071001400512 +200,000VND luc 19-06-2019 20:50:40. SD 83,157,241VND. Ref 863712.010819.091441.ntn 6890 FT19213990841750',
                true
            ],
            [
                '(FPTShop) Vi Momo 0763998413da nap so tien 500,000 d.Tang KH 20.000d 07K8FW3AYQ732 mua Phu kien tu 50.000d hoac Sim-Phan mem tu 80.000d HSD 10/07',
                false
            ]
        ];
    }

    /**
     * A basic test example.
     *
     * @param string $message
     * @param string $createdAt
     * @param $expected
     *
     * @return void
     * @dataProvider parseFptShopSmsProvider
     */
    public function testParseFptShopSms($message, $createdAt, $expected)
    {
        $instance = new SmsNotifierParser();
        $this->assertEquals($expected, $instance->parseFptShopSms($message, $createdAt));
    }

    public function parseFptShopSmsProvider()
    {
        return [
            [
                '(FPTShop) Vi Momo 01263998413da nap so tien 500,000 d.Tang KH 20.000d 09X2X7NBCJ726 mua Phu kien tu 50.000d hoac Sim-Phan mem tu 80.000d HSD 10/09',
                '2019/08/14 11:00',
                '[MoMo] Nhận được số tiền `500,000đ` từ `01263998413` (FPTShop) vào lúc 2019/08/14 11:00'
            ],
            [
                '(FPTShop) Vi Momo 01263998413da nap so tien 200,000 d.Tang KH 20.000d 09X2X7NBCJ726 mua Phu kien tu 50.000d hoac Sim-Phan mem tu 80.000d HSD 10/09',
                '2019/08/14 11:00',
                '[MoMo] Nhận được số tiền `200,000đ` từ `01263998413` (FPTShop) vào lúc 2019/08/14 11:00'
            ],
            [
                'DongA Bank thong bao: TK 0110666501 da thay doi: +200,000 VND. 863712.010819.091441.ntn 6890 FT19213990841750. So du hien tai la:...',
                '2019/08/14 11:00',
                null
            ],
            [
                'SD TK 0071001400512 +200,000VND luc 19-06-2019 20:50:40. SD 83,157,241VND. Ref 863712.010819.091441.ntn 6890 FT19213990841750',
                '2019/08/14 11:00',
                null
            ],

        ];
    }
}
