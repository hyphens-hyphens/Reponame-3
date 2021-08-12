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
                    // "created_at" => now(),
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

    public function index(Request $request)
    {
        $clientIp = $this->GetClientIp();
        $partners_ip = config('t2g_common.customers_ip.partners_ip');

        if (!is_null($partners_ip) && count($partners_ip) > 0 && !in_array($clientIp, $partners_ip)) {
            return response('Unauthorized.', 401);
        }

        $limit = $request->input('limit');
        $type = $request->input('type');

        if (is_null($limit)) {
            $limit = PHP_INT_MAX;
        }

        $items = $this->ipCustomerRepository->paginate($limit)->items();
        $data = array_map(function ($item) {
            return $item["ip"];
        }, $items);

        $plainIps = implode("\n", $data);
        if ($type == "file") {
            // return an string as a file to the user
            $fileName = strval(now());
            $response = response($plainIps, 200);
            $response->header('Content-Type', 'application/octet-stream');
            $response->header('Content-Disposition', 'attachment; filename="' . $fileName . '.txt"');
            return $response;
        }
        return response($plainIps, 200)->header('Content-Type', 'text/plain');
    }

    private function getClientIp()
    {
        $all_ip = "";
        $client_ip = "";

        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $all_ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $all_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            $all_ip = $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            $all_ip = $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            $all_ip = $_SERVER["HTTP_FORWARDED"];
        } else {
            $all_ip = $_SERVER["REMOTE_ADDR"];
        }

        // Get ip v4 if avalibel
        $ips = explode(" ", $all_ip);
        if (count($ips) > 0) {
            $client_ip = $ips[0];
            foreach ($ips as $key => $value) {
                if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $client_ip = $value;
                    break;
                }
            }
        }

        return $client_ip;
    }
}
