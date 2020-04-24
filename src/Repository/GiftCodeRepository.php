<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\GiftCode;

/**
 * Class GiftCodeRepository
 *
 * @package \T2G\Common\Repository
 */
class GiftCodeRepository extends AbstractEloquentRepository
{
    /**
     * @return string
     */
    public function model(): string
    {
        return GiftCode::class;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
//            GiftCode::TYPE_PER_SERVER    => "Code theo server",
            GiftCode::TYPE_PER_ACCOUNT   => "Code theo account",
//            GiftCode::TYPE_PER_CHARACTER => "Code theo character",
        ];
    }

}
