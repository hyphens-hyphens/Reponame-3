<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class MultiplePCDetectionService
 *
 * @package \T2G\Common\Services
 */
class MultiplePCDetectionService extends AbstractKibanaService
{
    const INDEX_PREFIX_PRIVATE_CHAT = 'private_chat';

    /**
     * @param \DateTime $from
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getMultiplePCTeamUpInPrivateChat(\DateTime $from)
    {
        $query = [
            "size"  => 0,
            "sort"  => [
                "@timestamp" => ["order" => "asc"]
            ],
            "aggs" => [
                "filter_data" => [
                    "filter" => [
                        "bool" => [
                            "filter" => [
                                [
                                    "range" => [
                                        "@timestamp" => [
                                            "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))->format('c'),
                                        ],
                                    ]
                                ],
                                [
                                    "term" => [
                                        "keoxe" => true
                                    ],
                                ],
                            ]
                        ]
                    ],
                    "aggs" => [
                        "server" => [
                            "terms" => [
                                "field" => "jx_server"
                            ],
                            "aggs" => [
                                "char1" => [
                                    "terms" => [
                                        "field" => "char1.keyword"
                                    ],
                                    "aggs" => [
                                        "char2" => [
                                            "terms" => [
                                                "field" => "char2.keyword"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_PRIVATE_CHAT),
            'body'  => $query,
        ];
        $data = $this->es->search($params);

        return new SearchResult($data);
    }
}
