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
            GiftCode::TYPE_PER_ACCOUNT => "Code theo account",
            GiftCode::TYPE_PER_MONTH   => "Code tháng",
        ];
    }

    public function getAvailableGiftcodes()
    {
        $query = $this->query();
        $query->where('status', 1)
            ->where('is_claimable', 1)
            ->orderBy('updated_at', 'desc')
        ;

        return $query->get();
    }

    /**
     * @return array
     */
    public function getGiftCodeTypes()
    {
        $query = $this->query();
        $codes = $query->where('status', 1)
            ->orderBy('id', 'ASC')
            ->get();
        if (!$codes) {
            return [];
        }

        return array_column($codes->toArray(), 'name', 'id');
    }

}
