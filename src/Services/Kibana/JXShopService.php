<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class GoldWithdrawingService
 *
 * @package \T2G\Common\Services\Kibana
 */
class JXShopService extends AbstractKibanaService
{
    const INDEX_PREFIX_SHOP = 'shop';

    /**
     * @param \DateTime $from
     * @param int       $size
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getShopLogs(\DateTime $from, $size = 10000)
    {
        $query = [
            "query" => [
                "range" => [
                    "@timestamp" => [
                        "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c'),
                    ],
                ],
            ],
            "size"  => $size,
            "sort"  => [
                "@timestamp" => ["order" => "asc"]
            ]
        ];
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_SHOP),
            'body'  => $query,
        ];
        $data = $this->es->search($params);

        return new SearchResult($data);
    }
}
