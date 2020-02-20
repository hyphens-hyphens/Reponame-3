<?php

namespace T2G\Common\Services\Kibana;

use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class GoldWithdrawingService
 *
 * @package \T2G\Common\Services\Kibana
 */
class TradingMonitoringService extends AbstractKibanaService
{
    const INDEX_PREFIX_GOLD_TRADING = 'trade_task_item';
    const INDEX_PREFIX_MONEY_TRADING = 'trade_tienvan';

    /**
     * @param \DateTime $from
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getGoldTradingByOwnerLogs(\DateTime $from)
    {
        // todo: refactor getGoldTradingByOwnerLogs and getGoldTradingByReceiverLogs to 1 function
        $query  = [
            "aggs" => [
                "filter_aggs" => [
                    "filter" => [
                        "bool" => [
                            "filter" => [
                                [
                                    "range" => [
                                        "@timestamp" => [
                                            "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))
                                                ->format('c'),
                                        ],
                                    ],
                                ],
                                [
                                    "term"  => [
                                        "is_gold" => true,
                                    ],
                                ]
                            ],

                        ]
                    ],
                    "aggs" => [
                        "server" => [
                            "terms" => ["field" => "jx_server"],
                            "aggs"   => [
                                "char1" => [
                                    "terms" => ["field" => "char1.keyword"],
                                    "aggs"  => [
                                        "char2" => [
                                            "terms" => ["field" => "char2.keyword"],
                                            "aggs" => [
                                                "total_gold" => [
                                                    "sum" => ["field" => "quantity"],
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ],
            "size" => 0,
        ];

        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_GOLD_TRADING),
            'body'  => $query,
        ];
        $data   = $this->es->search($params);

        return new SearchResult($data);
    }

    /**
     * @param \DateTime $from
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getGoldTradingByReceiverLogs(\DateTime $from)
    {
        $query  = [
            "aggs" => [
                "filter_aggs" => [
                    "filter" => [
                        "bool" => [
                            "filter" => [
                                [
                                    "range" => [
                                        "@timestamp" => [
                                            "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))
                                                ->format('c'),
                                        ],
                                    ],
                                ],
                                [
                                    "term"  => [
                                        "is_gold" => true,
                                    ],
                                ]
                            ],

                        ]
                    ],
                    "aggs" => [
                        "server" => [
                            "terms" => ["field" => "jx_server"],
                            "aggs"   => [
                                "char2" => [
                                    "terms" => ["field" => "char2.keyword"],
                                    "aggs"  => [
                                        "char1" => [
                                            "terms" => ["field" => "char1.keyword"],
                                            "aggs" => [
                                                "total_gold" => [
                                                    "sum" => ["field" => "quantity"],
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ],
            "size" => 0,
        ];

        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_GOLD_TRADING),
            'body'  => $query,
        ];
        $data   = $this->es->search($params);

        return new SearchResult($data);
    }

    /**
     * @param \DateTime $from
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getMoneyTradingByOwnerLogs(\DateTime $from)
    {
        $query  = [
            "aggs" => [
                "filter_aggs" => [
                    "filter" => [
                        "bool" => [
                            "filter" => [
                                [
                                    "range" => [
                                        "@timestamp" => [
                                            "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))
                                                ->format('c'),
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                    "aggs" => [
                        "server" => [
                            "terms" => ["field" => "jx_server"],
                            "aggs"   => [
                                "char1" => [
                                    "terms" => ["field" => "char1.keyword"],
                                    "aggs"  => [
                                        "char2" => [
                                            "terms" => ["field" => "char2.keyword"],
                                            "aggs" => [
                                                "total_money" => [
                                                    "sum" => ["field" => "amount"],
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ],
            "size" => 0,
        ];

        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_MONEY_TRADING),
            'body'  => $query,
        ];
        $data   = $this->es->search($params);

        return new SearchResult($data);
    }

    /**
     * @param \DateTime $from
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function getMoneyTradingByReceiverLogs(\DateTime $from)
    {
        $query  = [
            "aggs" => [
                "filter_aggs" => [
                    "filter" => [
                        "bool" => [
                            "filter" => [
                                [
                                    "range" => [
                                        "@timestamp" => [
                                            "gte" => $from->setTimezone(new \DateTimeZone(config('app.timezone')))
                                                ->format('c'),
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                    "aggs" => [
                        "server" => [
                            "terms" => ["field" => "jx_server"],
                            "aggs"   => [
                                "char2" => [
                                    "terms" => ["field" => "char2.keyword"],
                                    "aggs"  => [
                                        "char1" => [
                                            "terms" => ["field" => "char1.keyword"],
                                            "aggs" => [
                                                "total_money" => [
                                                    "sum" => ["field" => "amount"],
                                                ],
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ],
            "size" => 0,
        ];

        $params = [
            'index' => $this->getIndex(self::INDEX_PREFIX_MONEY_TRADING),
            'body'  => $query,
        ];
        $data   = $this->es->search($params);

        return new SearchResult($data);
    }
}
