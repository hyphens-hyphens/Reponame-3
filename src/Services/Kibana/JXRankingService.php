<?php

namespace T2G\Common\Services\Kibana;


class JXRankingService extends AbstractRankingService
{
    const TOPLIST_INDEX_PREFIX = 'toplist';

    /**
     * @return string
     */
    protected function getIndexPrefix(): string
    {
        return self::TOPLIST_INDEX_PREFIX;
    }
}
