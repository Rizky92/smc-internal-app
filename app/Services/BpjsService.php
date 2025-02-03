<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use LZCompressor\LZString;

class BpjsService
{
    protected string $timestamp;

    protected ?Response $response = null;

    protected array $headers;

    public function __construct(?string $timestamp = null)
    {
        $this->timestamp = $timestamp ?? now()->format('U');

        $this->headers = [
            'x-cons-id'   => config('bpjs.consid'),
            'x-timestamp' => $this->timestamp,
            'x-signature' => $this->generateSignature(),
            'user_key'    => config('bpjs.userkey'),
        ];
    }

    protected function generateSignature(): string
    {
        $consid = config('bpjs.consid');
        $secret = config('bpjs.secret');

        $signature = hash_hmac('sha256', "{$consid}&{$this->timestamp}", $secret, true);

        return base64_encode($signature);
    }

    protected function decryptResponse(?string $key = null): Collection
    {
        $consid = config('bpjs.consid');
        $secret = config('bpjs.secret');

        $hash = hex2bin(hash('sha256', $consid.$secret.$this->timestamp));

        $iv = substr($hash, 0, 16);

        $response = base64_decode($this->response[$key]);

        $output = openssl_decrypt($response, 'AES-256-CBC', $hash, OPENSSL_RAW_DATA, $iv);

        $decoded = LZString::decompressFromEncodedURIComponent($output) ?? '';

        $this->response['decoded'] ??= json_decode(
            $decoded,
            $associative = true,
            $depth = 512,
            JSON_OBJECT_AS_ARRAY
        );

        return $this->response->collect($key);
    }
}
