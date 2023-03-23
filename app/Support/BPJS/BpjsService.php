<?php

namespace App\Support\BPJS;

use Illuminate\Support\Facades\Http;
use LZCompressor\LZString;
use RuntimeException;

class BpjsService
{
    protected string $timestamp = '';

    protected array $response;

    protected array $headers;

    protected $consid = '8645';

    protected $secret = '0tG8DF7F13';

    protected $userkey = '568884b9a78e0af6bc518f7f4ecd8b4c';

    public static function start()
    {
        return new static;
    }

    public function getListTask($noBooking)
    {
        if (empty($this->timestamp)) {
            $this->timestamp = now()->format('U');
        }
        
        $url = 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/antrean/getlisttask';

        $this->setHeaders([
            'x-cons-id' => $this->consid,
            'x-timestamp' => $this->timestamp,
            'x-signature' => $this->generateSignature(),
            'user_key' => $this->userkey,
        ]);

        $data = $noBooking;

        if (is_string($noBooking)) {
            $data = ['kodebooking' => '20230309000001'];
        }

        $this->response = Http::withHeaders($this->headers)->post($url, $data)->json();
        
        $this->decryptResponse();

        return $this;
    }

    protected function generateSignature()
    {
        $timestamp = $this->timestamp;

        $consid = $this->consid;
        $secret = $this->secret;

        $signature = hash_hmac('sha256', "{$consid}&{$timestamp}", $secret, true);

        return base64_encode($signature);
    }

    protected function decryptResponse(string $key = 'response')
    {
        $timestamp = $this->timestamp;
        $consid = $this->consid;
        $secret = $this->secret;

        $hash = hex2bin(hash('sha256', $consid . $secret . $timestamp));

        $iv = substr($hash, 0, 16);

        $response = base64_decode($this->response[$key]);

        $output = openssl_decrypt($response, 'AES-256-CBC', $hash, OPENSSL_RAW_DATA, $iv);

        $decoded = LZString::decompressFromEncodedURIComponent($output);

        $this->response['decoded'] ??= json_decode(
            $decoded,
            $associative = true,
            $depth = 512,
            JSON_OBJECT_AS_ARRAY
        );
    }

    public function setTimestamp(string $timestamp = '')
    {
        if (empty($timestamp)) {
            $timestamp = now()->format('U');
        }

        $this->timestamp = $timestamp;

        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function setHeader(string $key, mixed $value = null)
    {
        $this->headers[$key] ??= $value;

        return $this;
    }

    public function response()
    {
        return $this->response;
    }
}
