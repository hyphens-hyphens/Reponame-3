<?php

namespace T2G\Common\Services\Kibana;

use Elasticsearch\ClientBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use T2G\Common\Models\ElasticSearch\SearchResult;

/**
 * Class KibanaQueryService
 *
 * @package \T2G\Common\Services
 */
abstract class AbstractKibanaService
{
    const MAX_RESULTS_WINDOW = 500000;
    const MAX_RESULTS_WINDOW_INNER = 10000;
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
    protected function getIndex(string $prefix)
    {
        return $prefix . config('t2g_common.kibana.index_suffix');
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

    /**
     * @param       $items
     * @param null  $perPage
     * @param null  $page
     * @param array $options
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function paginate($items, $total, $perPage = null, $page = null, $options = [])
    : LengthAwarePaginator {
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items, $total, $perPage, $page, $options);
    }
}
