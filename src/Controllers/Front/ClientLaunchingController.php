<?php

namespace T2G\Common\Controllers\Front;

use T2G\Common\Models\ClientLaunching;
use T2G\Common\Repository\ClientLaunchingRepository;

/**
 * Class ClientLaunchingController
 *
 * @package \T2G\Common\Controllers\Front
 */
class ClientLaunchingController extends BaseFrontController
{
    /**
     * @var \T2G\Common\Repository\ClientLaunchingRepository
     */
    protected $repository;

    /**
     * ClientLaunchingController constructor.
     *
     * @param \T2G\Common\Repository\ClientLaunchingRepository $repository
     */
    public function __construct(ClientLaunchingRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function start()
    {
        $ethernetMAC = request('EthernetMAC');
        $wifiMAC = request('WifiMAC');
        list($version, $host) = $this->extractVersionAndHost();
        $data = request()->all();
        /** @var \T2G\Common\Models\ClientLaunching|null $record */
        $signature = $this->repository->makeClientSignature($ethernetMAC, $wifiMAC, $version, $host);
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
        ];
        list($data['version'], $data['host']) = $this->extractVersionAndHost();

        return $this->repository->create($data);
    }

    /**
     * @param \T2G\Common\Models\ClientLaunching $record
     * @param                                    $data
     */
    private function updateClientRecord(ClientLaunching $record, $data)
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
