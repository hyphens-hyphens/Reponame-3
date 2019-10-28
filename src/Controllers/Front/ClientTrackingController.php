<?php

namespace T2G\Common\Controllers\Front;

use T2G\Common\Models\ClientTracking;
use T2G\Common\Repository\ClientTrackingRepository;

/**
 * Class ClientTrackingController
 *
 * @package \T2G\Common\Controllers\Front
 */
class ClientTrackingController extends BaseFrontController
{
    /**
     * @var \T2G\Common\Repository\ClientTrackingRepository
     */
    protected $repository;

    /**
     * ClientLaunchingController constructor.
     *
     * @param \T2G\Common\Repository\ClientTrackingRepository $repository
     */
    public function __construct(ClientTrackingRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function start()
    {
        $ethernetMAC = request('EthernetMAC');
        $wifiMAC = request('WifiMAC');
        $data = request()->all();
        list($data['version'], $data['host']) = $this->extractVersionAndHost();
        /** @var \T2G\Common\Models\ClientTracking|null $record */
        $signature = $this->repository->makeClientSignature($ethernetMAC, $wifiMAC, $data['version'], $data['host']);
        $record = $this->repository->getRecordByClientSignature($signature);
        if (!$record) {
            $record = $this->createNewRecord($data);
        } else {
            $this->updateClientRecord($record, $data);
        }

        return response("Processed: " . $record->id);
    }

    /**
     * @param $data
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Throwable
     */
    private function createNewRecord($data)
    {
        $data = [
            'ethernet_mac' => $data['EthernetMAC'] ?? '',
            'wifi_mac'     => $data['WifiMAC'] ?? '',
            'local_ip'     => $data['localIP'] ?? '',
            'external_ip'  => $data['extIP'] ?? '',
            'version'      => $data['version'] ?? '',
            'host'         => $data['host'] ?? '',
        ];

        return $this->repository->create($data);
    }

    /**
     * @param \T2G\Common\Models\ClientTracking  $record
     * @param                                    $data
     */
    private function updateClientRecord(ClientTracking $record, $data)
    {
        $record->local_ip    = $data['localIP'] ?? '';
        $record->external_ip = $data['extIP'] ?? '';
        $record->host        = $data['host'] ?? '';

        $record->save();
    }

    /**
     * @return array []
     */
    private function extractVersionAndHost()
    {
        $version = request('version', '');
        $result = [$version, ''];
        if ($version && $version != 'none') {
            $versionArray = explode('-', $version);
            if (count($versionArray) < 2) {
                return $result;
            }
            $host = last($versionArray);
            unset($versionArray[count($versionArray) - 1]);
            $version = implode('-', $versionArray);

            $result = [$version, $host];
        }

        return $result;
    }
}
