<?php

namespace T2G\Common\Services;

use Elasticsearch\ClientBuilder;
use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class KibanaQueryService
 *
 * @package \T2G\Common\Services
 */
class KibanaQueryService
{
    const INDEX_PREFIX_ACTIVE_USER = 'savehwid';

    /**
     * @var \Elasticsearch\Client
     */
    protected $es;

    public function __construct()
    {
        $configs = config('t2g_common.kibana.elasticsearch_config');
        $this->es = ClientBuilder::fromConfig($configs);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    private function getIndex(string $prefix)
    {
        return $prefix . config('t2g_common.kibana.index_suffix');
    }

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
                        "gte" => $start->format('c'),
                        "lt"  => $end->format('c'),
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
     * @param        $scrollId
     * @param string $time
     *
     * @return \T2G\Common\Models\ElasticSearch\SearchResult
     */
    public function scroll($scrollId, $time = "1m")
    {
        $data = $this->es->scroll([
            'body' => [
                'scroll_id' => $scrollId,
            ],
            'scroll'=> $time,
        ]);

        return new SearchResult($data);
    }
}
