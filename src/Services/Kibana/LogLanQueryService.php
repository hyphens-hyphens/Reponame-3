<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class LogLanQueryService
 *
 * @package \T2G\Common\Services\Kibana
 */
class LogLanQueryService extends AbstractKibanaService
{
    const INDEX_PREFIX_LOG_LAN = 'log_lan';

    /**
     * @param array          $usernames
     * @param null           $server
     * @param \DateTime|null $time
     *
     * @return array
     * @throws \Exception
     */
    public function getIpLanByUsernames(
        array $usernames,
        $server = null,
        \DateTime $time = null
    )
    {
        $query = $this->getIpLabByUsernamesAggregation($usernames, 1);
        if ($time) {
            $query['aggs']['filter_data']['filter']['bool']['filter'][] = [
                'range' => [
                    '@timestamp' => [
                        'lte' => $time->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c'),
                    ]
                ]
            ];
        }
        if ($server) {
            $query['aggs']['filter_data']['filter']['bool']['filter'][] = [
                'term' => [
                    'jx_server' => $server
                ]
            ];
        }

        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_LOG_LAN),
            'body'  => $query,
        ];
        $data = $this->es->search($params);
        $searchResult = new SearchResult($data);
        $aggs = $searchResult->getAggs('filter_data');
        $data = [];
        foreach ($aggs['user']['buckets'] as $row) {
            foreach ($row['top']['hits']['hits'] as $hit) {
                $data[$row['key']] = $hit['_source']['ip'];
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
    protected function getIpLabByUsernamesAggregation(array $usernames, int $size = 1)
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
