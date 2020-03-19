<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class MultiplePCDetectionService
 *
 * @package \T2G\Common\Services
 */
class KimYenKeoXeDetectionService extends AbstractKibanaService
{
    const INDEX_PREFIX_MOVE_MAP = 'move_map';

    /**
     * @param \DateTime $from
     * @param int       $size
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getMoveMapLogs(\DateTime $from, $size = 500000)
    {
        $query = [
            "query" => [
                "bool" => [
                    "filter" => [
                        [
                            "range" => [
                                "@timestamp" => [
                                    "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c'),
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            "size"  => $size,
            "sort"  => [
                "@timestamp" => ["order" => "asc"]
            ],
            "_source" => ['jx_server', '@timestamp', 'user', 'char', 'map_id', 'map_name', 'action', 'level', 'hwid']
        ];
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_MOVE_MAP),
            'body'  => $query,
        ];
        $data = $this->es->search($params);

        return new SearchResult($data);
    }
}
