<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class AccountService
 *
 * @package \T2G\Common\Services\Kibana
 */
class AccountService extends AbstractKibanaService
{
    const INDEX_PREFIX_ACTIVE_USER = 'savehwid';

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int       $size
     * @param string    $scroll
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getActiveUsersInAPeriod(\DateTime $start, \DateTime $end, $size = 1000, $scroll = '')
    {
        $query = [
            "query" => [
                "range" => [
                    "@timestamp" => [
                        "gte" => $start->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c'),
                        "lt"  => $end->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c'),
                    ],
                ],
            ],
            "size"  => $size,
            "sort"  => [
                "@timestamp" => ["order" => "asc"]
            ]
        ];
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_ACTIVE_USER),
            'body'  => $query,
        ];
        if ($scroll) {
            $params['scroll'] = $scroll;
        }
        $data = $this->es->search($params);

        return new SearchResult($data);
    }

    public function getUsernameByChar(int $server, $char)
    {
        $query = [
            "size" => 1,
            "query" => [
                "bool" => [
                    "filter" => [
                        [
                            "term" => [
                                "jx_server" => $server
                            ]
                        ],
                        [
                            "term" => [
                                "char.keyword" => $char
                            ]
                        ]
                    ]
                ]
            ],
            "sort" => [
                "@timestamp" => "desc"
            ],
            "_source" => ["user", "char", "level", "jx_server"]
        ];
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_ACTIVE_USER),
            'body'  => $query,
        ];
        $data = $this->es->search($params);

        $searchResult = new SearchResult($data);
        if (!$searchResult->getTotalResults()) {
            return null;
        }
        $hit = array_first($searchResult->getHits());

        return $hit['_source'];
    }
}
