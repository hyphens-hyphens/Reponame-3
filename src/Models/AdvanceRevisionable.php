<?php

namespace T2G\Common\Models;

use Venturecraft\Revisionable\Revisionable;

/**
 * Class AdvanceRevisionable
 *
 * @package \T2G\Common\Models
 */
trait AdvanceRevisionable
{
    /**
     * @return mixed
     */
    public function advancedRevisionHistory()
    {
        return $this->morphMany(get_class(Revisionable::newModel()), 'revisionable')->orderBy('created_at', 'DESC');
    }
}
