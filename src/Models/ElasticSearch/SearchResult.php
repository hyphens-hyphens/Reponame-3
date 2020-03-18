<?php
namespace T2G\Common\Models\ElasticSearch;

/**
 * Class SearchResult
 *
 * @package \\${NAMESPACE}
 */
class SearchResult
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }

    /**
     * @return string|null
     */
    public function getScrollId()
    {
        return $this->data['_scroll_id'] ?? null;
    }

    /**
     * @return array
     */
    public function getHits()
    {
        return isset($this->data['hits']['hits']) ? $this->data['hits']['hits'] : [];
    }

    /**
     * @return int
     */
    public function getTotalResults()
    {
        return isset($this->data['hits']['total']['value']) ? $this->data['hits']['total']['value'] : 0;
    }

    /**
     * @param string $rootAggs
     *
     * @return array|mixed
     */
    public function getAggs($rootAggs = '')
    {
        $data = isset($this->data['aggregations']) ? $this->data['aggregations'] : [];
        if ($rootAggs && isset($data[$rootAggs])) {
            return $data[$rootAggs];
        }

        return $data;
    }
}
