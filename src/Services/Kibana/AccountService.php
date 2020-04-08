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

    /**
     * @param int $server
     * @param     $char
     *
     * @return array|null
     */
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

    /**
     * return ex array:5 [
     *       "usernameX" => "93C39-E0310-D1AA5-41A7D-54976-F8E25-6306D-B0107"
     *       ...
     *   ]
     *
     * @param array $usernames
     *
     * @return array
     */
    public function getHwidByUsernames(array $usernames)
    {
        $query = $this->getHwidByUsernamesAggregation($usernames, 1);
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_ACTIVE_USER),
            'body'  => $query,
        ];
        $data = $this->es->search($params);
        $searchResult = new SearchResult($data);
        $aggs = $searchResult->getAggs('filter_data');
        $data = [];
        foreach ($aggs['user']['buckets'] as $row) {
            $data[$row['key']] = $row['top']['hits']['hits'][0]['_source']['hwid'];
        }

        return $data;
    }

    /**
     * @param array $usernames
     * @param int   $sizeHwids
     * @param null  $days
     *
     * @return array
     * @throws \Exception
     */
    public function getHwidLogsByUsernames(array $usernames, $sizeHwids = 1, $days = null)
    {
        $query = $this->getHwidByUsernamesAggregation($usernames, $sizeHwids);
        if ($days) {
            $date = new \DateTime("-{$days} days", new \DateTimeZone(config('app.timezone')));
            $query['aggs']['filter_data']['filter']['bool']['filter'][] = [
                'range' => [
                    '@timestamp' => [
                        'gte' => $date->format('c')
                    ]
                ]
            ];
        }
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_ACTIVE_USER),
            'body'  => $query,
        ];
        $data = $this->es->search($params);
        $searchResult = new SearchResult($data);
        $aggs = $searchResult->getAggs('filter_data');
        $data = [];
        foreach ($aggs['user']['buckets'] as $row) {
            foreach ($row['top']['hits']['hits'] as $hit) {
                $data[$row['key']][] = [
                    'time' => new \DateTime($hit['_source']['@timestamp']),
                    'hwid' => $hit['_source']['hwid'],
                    'char' => $hit['_source']['char'],
                    'level' => $hit['_source']['level'],
                ];
            }
        }

        return $data;
    }

    /**
     * @param array $usernames
     * @param int   $size
     *
     * @return array
     */
    private function getHwidByUsernamesAggregation(array $usernames, int $size = 1)
    {
        return $query = [
            "size" => 0,
            "aggs" => [
                "filter_data" => [
                    "filter" => [
                        "bool" => [
                            "filter" => [
                                [
                                    "terms" => [
                                        "user.keyword" => $usernames
                                    ]
                                ]
                            ],
                        ],
                    ],
                    "aggs" => [
                        "user" => [
                            "terms" => [
                                "field" => "user.keyword"
                            ],
                            "aggs" => [
                                "top" => [
                                    "top_hits" => [
                                        "sort" => [
                                            "@timestamp" => ["order" => "desc"]
                                        ],
                                        "_source" => [
                                            "includes" => ['user', 'hwid', '@timestamp', 'jx_server', 'level', 'char'],
                                        ],
                                        "size" => $size
                                    ],
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ];
    }
}
