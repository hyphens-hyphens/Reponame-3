<?php

namespace T2G\Common\Models;

/**
 * Class ClientLaunching
 *
 * @package \T2G\Common\Models
 */
class ClientLaunching extends BaseEloquentModel
{
    protected $table = "client_launching";

    protected $fillable = ['ethernet_mac', 'wifi_mac', 'version', 'host', 'signature', 'local_ip', 'external_ip'];

}
