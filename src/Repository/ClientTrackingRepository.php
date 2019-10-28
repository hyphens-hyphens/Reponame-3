<?php

namespace T2G\Common\Repository;

use T2G\Common\Models\ClientTracking;

/**
 * Class ClientTrackingRepository
 *
 * @package \T2G\Common\Repository
 */
class ClientTrackingRepository extends AbstractEloquentRepository
{

    /**
     * @return string
     */
    public function model(): string
    {
        return ClientTracking::class;
    }

    public function create(array $data)
    {
        $data['signature'] = $this->makeClientSignature($data['ethernet_mac'], $data['wifi_mac'], $data['version'], $data['host']);

        return parent::create($data);
    }

    /**
     * @param $signature
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getRecordByClientSignature($signature)
    {
        $query = $this->query()->where('signature', $signature);

        return $query->first();
    }

    /**
     * @param        $ethernetMac
     * @param        $wifiMac
     * @param        $version
     * @param string $host
     *
     * @return string
     */
    public function makeClientSignature($ethernetMac, $wifiMac, $version, $host = '')
    {
        return sha1("{$ethernetMac} {$wifiMac} {$version} {$host}");
    }
}
