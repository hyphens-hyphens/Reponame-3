<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class MultiplePCDetectionService
 *
 * @package \T2G\Common\Services
 */
class MultipleLoginDetectionService extends AbstractKibanaService
{
    const INDEX_PREFIX_MULTI_LOGIN = 'multi_login';

    /**
     * @param \DateTime          $from
     * @param \DateInterval|null $interval
     * @param int                $size
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getMultipleLoginLogs(\DateTime $from, \DateInterval $interval = null, $size = 15000)
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
        ];
        if ($interval) {
            $query['query']['bool']['filter'][0]['range']['@timestamp']['lt'] = $from->add($interval)->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c');
        }
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_MULTI_LOGIN),
            'body'  => $query,
        ];
        $data = $this->es->search($params);

        return new SearchResult($data);
    }
}
