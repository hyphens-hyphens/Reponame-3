<?php

namespace T2G\Common\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class CustomerIPController
 *
 * @package T2G\Common\Controllers\Api
 */
class CustomerIPController extends Controller
{
    const DEFAULT_PER_PAGE = 100;
    const PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDECcb+37LoGr9n87swkuwnEPxf 2e3azDk8ej5w/gz49YtaQzPjupsJECUbI0EX2261q1Ja1kbwRr+3cCYENw4bQCLr KtWS7xAbCU95itVRFeUXHx12z4GSn6ArGTfIhMUFQD3cDwsexez2f/ywDrB18uxz UC/YsJDekMmyie6O4QIDAQAB\n-----END PUBLIC KEY-----";

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
            // Verify signature (use the same algorithm used to sign the msg).
            $ok = openssl_verify($data['data'], base64_decode($data['signature']), $key, OPENSSL_ALGO_SHA1);
            if ($ok == 1) {
                \Log::info("Verified");
                // TODO SAVE IP
                $result = true;
            } elseif ($ok == 0) {
                \Log::info("Unverified");
            } else {
                \Log::info("Unknown verification response");
            }
        }

        return response()->json($result);
    }

    public function delete()
    {
        return true;
    }

    public function index($limit = self::DEFAULT_PER_PAGE)
    {
        return ["123.11.15.117", "123.11.15.119"];
    }
}
