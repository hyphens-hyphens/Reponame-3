<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\Revision;

/**
 * Class RevisionRepository
 *
 * @package \T2G\Common\Repository
 */
class RevisionRepository extends AbstractEloquentRepository
{

    /**
     * @return string
     */
    public function model(): string
    {
        return Revision::class;
    }

}
