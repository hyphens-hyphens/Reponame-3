<?php
namespace T2G\Common\Services\Kibana;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use T2G\Common\Models\ElasticSearch\SearchResult;
use T2G\Common\Services\Kibana\AbstractKibanaService;

abstract class AbstractRankingService extends AbstractKibanaService
{
    public const MAX_RANKING_LISTING    = 100;

    /**
     * @return string
     */
    abstract protected function getIndexPrefix(): string;

    /**
     * @return string[]
     */
    protected function getSourceFields(): array
    {
        return ["user", "char", "level", "exp"];
    }

    /**
     * @return array
     */
    protected function getQueryFilter($server = '')
    {
        $filter = [
            "bool" => [
                "filter" => [
                    [
                        "range" => [
                            "@timestamp" => [
                                "gt"        => "now-24h",
                                "time_zone" => "+07:00",
                            ],
                        ],
                    ],
                    [
                        "term" => [
                            "server_name.keyword" => trim($server)
                        ]
                    ]
                ],
            ],
        ];

        return $filter;
    }

    /**
     * @param int $pageSize
     * @param int $page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTopLevelList($server = '', $pageSize = 10, $page = 1): LengthAwarePaginator
    {
        $sort = [
            "log.file.path.keyword" => [
                "order" => "desc",
            ],
            "level" => [
                "order" => "desc",
            ],
            "exp"   => [
                "order" => "desc",
            ],
        ];

        return $this->getTopListQueryResults($server, $sort, $page, $pageSize);
    }

    /**
     * @param int $page
     * @param int $pageSize
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTopMoneyList($pageSize = 15, $page = null): LengthAwarePaginator
    {
        $sort = [
            "log.file.path.keyword" => [
                "order" => "desc",
            ],
            "money" => [
                "order" => "desc",
            ],
        ];

        return $this->getTopListQueryResults($sort, $page, $pageSize);
    }

    /**
     * @param      $sortBy
     * @param int  $pageSize
     * @param null $page
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getTopListQueryResults($server = '', $sortBy, $page = 1, $pageSize = 15): LengthAwarePaginator
    {
        $pageSize = $pageSize > self::MAX_RESULTS_WINDOW ? self::MAX_RESULTS_WINDOW : $pageSize;
        $from = ($page - 1) * $pageSize;
        $queryFilter = $this->getQueryFilter($server);
        $query = [
            "query"   => $queryFilter,
            "sort"    => $sortBy,
            "_source" => $this->getSourceFields(),
            "from"    => $from,
            "size"    => ($pageSize + $from) > self::MAX_RANKING_LISTING ? self::MAX_RANKING_LISTING - $from : $pageSize,
        ];
        $params       = [
            'index' => $this->getIndex($this->getIndexPrefix()),
            'body'  => $query,
        ];
        $data         = $this->es->search($params);
        $searchResult = new SearchResult($data);
        $data         = [];
        foreach ($searchResult->getHits() as $row) {
            $record = [];
            foreach ($this->getSourceFields() as $field) {
                $record[$field] = $row['_source'][$field];
            }
            $data[] = $record;
        }

        return $this->paginate($data, self::MAX_RANKING_LISTING, $pageSize, $page);
    }
}
