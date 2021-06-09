<?php

namespace T2G\Common\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use T2G\Common\Repository\IpCustomerRepository;

/**
 * Class CustomerIPController
 *
 * @package T2G\Common\Controllers\Api
 */
class CustomerIPController extends Controller
{
    const PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDECcb+37LoGr9n87swkuwnEPxf 2e3azDk8ej5w/gz49YtaQzPjupsJECUbI0EX2261q1Ja1kbwRr+3cCYENw4bQCLr KtWS7xAbCU95itVRFeUXHx12z4GSn6ArGTfIhMUFQD3cDwsexez2f/ywDrB18uxz UC/YsJDekMmyie6O4QIDAQAB\n-----END PUBLIC KEY-----";

    public $ipCustomerRepository;

    function __construct()
    {
        $this->ipCustomerRepository = app(IpCustomerRepository::class);
    }

    public function store(Request $request)
    {
        $result = false;
        $data = $request->json()->all();

        // Get public key.
        $key = openssl_pkey_get_public(self::PUBLIC_KEY);

        if ($key == 0) {
            \Log::info("Bad key zero.");
        } elseif ($key == false) {
            \Log::info("Bad key false.");
        } else {
            $hwid = $data['data'];
            // Verify signature (use the same algorithm used to sign the msg).
            $ok = openssl_verify($hwid, base64_decode($data['signature']), $key, OPENSSL_ALGO_SHA1);
            if ($ok == 1) {
                \Log::info("Verified");
                //  SAVE IP
                $clientIp = $this->GetClientIp();
                \Log::info("Ip: " . $clientIp);
                $this->ipCustomerRepository->createOrUpdate([
                    "ip" => $clientIp,
                    "hwid" => $hwid,
                    "status" => 1,
                    "created_at" => now(),
                    "updated_at" => now()
                ]);

                $result = true;
            } elseif ($ok == 0) {
                \Log::info("Unverified");
            } else {
                \Log::info("Unknown verification response");
            }
        }

        return response()->json($result);
    }

    public function index()
    {
        $result = $this->ipCustomerRepository->paginate();
        return response()->json($result);
    }

    private function getClientIp()
    {
        $ip_address = "";
        //whether ip is from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //whether ip is from remote address
        else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_address;
    }
}
