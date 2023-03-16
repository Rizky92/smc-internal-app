<?php

namespace App\Http\Controllers\BPJS;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use LZCompressor\LZString;

class MobileJKNController extends Controller
{
    public function __invoke()
    {
        $timestamp = now('UTC')->format('U');
        $consid = "8645";
        $secret = "0tG8DF7F13";
        $userkey = "568884b9a78e0af6bc518f7f4ecd8b4c";

        $signature = hash_hmac('sha256', "{$consid}&{$timestamp}", $secret, true);
        $signature = base64_encode($signature);

        $url = 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/antrean/getlisttask';

        $headers = [
            'x-cons-id' => $consid,
            'x-timestamp' => $timestamp,
            'x-signature' => $signature,
            'user_key' => $userkey,
        ];

        $data = ['kodebooking' => '20230309000001'];

        $getlisttask = Http::withHeaders($headers)->post($url, $data);

        $hash = hex2bin(hash('sha256', $consid . $secret . $timestamp));

        $iv = substr($hash, 0, 16);

        $response = base64_decode($getlisttask->json('response'));

        $output = openssl_decrypt($response, 'AES-256-CBC', $hash, OPENSSL_RAW_DATA, $iv);

        $decoded = LZString::decompressFromEncodedURIComponent($output);
        $decoded = json_decode($decoded, true, 512, JSON_OBJECT_AS_ARRAY);

        collect([
            'response'  => $getlisttask->json('response'),
            'timestamp' => $timestamp,
            'decoded'   => $decoded,
            'message'   => $getlisttask->reason(),
            'status'    => $getlisttask->status(),
            'object'    => $getlisttask,
        ])->dd();
    }
}
