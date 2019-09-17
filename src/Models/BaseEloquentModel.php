<?php

namespace T2G\Common\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseEloquentModel
 */
abstract class BaseEloquentModel extends Model
{
    use BaseEloquentModelTrait;
}
