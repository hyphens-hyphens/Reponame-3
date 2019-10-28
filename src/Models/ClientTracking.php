<?php

namespace T2G\Common\Models;

/**
 * Class ClientTracking
 *
 * @package \T2G\Common\Models
 */
class ClientTracking extends BaseEloquentModel
{
    protected $table = "client_tracking";

    protected $fillable = ['ethernet_mac', 'wifi_mac', 'version', 'host', 'signature', 'local_ip', 'external_ip'];
}
