<?php

namespace T2G\Common\Models;

/**
 * Class ClientTracking
 *
 * @package \T2G\Common\Models
 * @property int $id
 * @property string|null $version
 * @property string|null $host
 * @property string|null $ethernet_mac
 * @property string|null $wifi_mac
 * @property string|null $local_ip
 * @property string|null $external_ip
 * @property string $signature
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel active()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseEloquentModel orderByPublishDate()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereEthernetMac($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereExternalIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereLocalIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientTracking whereWifiMac($value)
 * @mixin \Eloquent
 */
class ClientTracking extends BaseEloquentModel
{
    protected $table = "client_tracking";

    protected $fillable = ['ethernet_mac', 'wifi_mac', 'version', 'host', 'signature', 'local_ip', 'external_ip'];
}
