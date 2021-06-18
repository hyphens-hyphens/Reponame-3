<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use TCG\Voyager\Traits\Resizable;

/**
 * Class FunctionalList
 * @package T2G\Common\Models
 */

class IpCustomer extends BaseEloquentModel
{
    protected $table = 'ip_customers';
    protected $fillable = [
        'ip',
        'status',
        'hwid',
        'note'
    ];
}
