<?php

namespace App\Support\BPJS;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LZCompressor\LZString;
use RuntimeException;

class BpjsService
{
    private const URL_DASHBOARD_PER_BULAN = "https://apijkn.bpjs-kesehatan.go.id/antreanrs/dashboard/waktutunggu/bulan/{bulan}/tahun/{tahun}/waktu/{waktu}";
    private const URL_GET_LIST_TASK = "https://apijkn.bpjs-kesehatan.go.id/antreanrs/antrean/getlisttask";

    protected string $timestamp;

    /** @var \Illuminate\Http\Client\Response|null */
    protected ?Response $response = null;

    /** @var array */
    protected array $headers;

    /**
     * @param  string|null $timestamp
     * 
     * @return void
     */
    public function __construct($timestamp = null)
    {
        $this->timestamp = $timestamp ?? now()->format('U');

        $this->headers = [
            'x-cons-id' => config('bpjs.consid'),
            'x-timestamp' => $this->timestamp,
            'x-signature' => $this->generateSignature(),
            'user_key' => config('bpjs.userkey'),
        ];
    }

    /**
     * @param  string $bulan
     * @param  string $tahun
     * @param  string $waktu
     * @psalm-param  "rs"|"server" $waktu
     * 
     * @return static
     */
    public function dashboardPerBulan(string $bulan, string $tahun, string $waktu): ?self
    {
        $url = str(self::URL_DASHBOARD_PER_BULAN)
            ->replace('{p1}', $bulan)
            ->replace('{p2}', $tahun)
            ->replace('{p3}', $waktu);
        
        $this->response = Http::withHeaders($this->headers)
            ->get((string) $url);

        $this->decryptResponse();

        return $this;
    }

    /**
     * @param  string $noBooking
     * 
     * @return static
     */
    public function getListTask($noBooking): ?self
    {
        $this->response = Http::withHeaders($this->headers)
            ->post(self::URL_GET_LIST_TASK, ['kodebooking' => $noBooking]);
        
        $this->decryptResponse();

        return $this;
    }

    protected function generateSignature(): string
    {
        $consid = config('bpjs.consid');
        $secret = config('bpjs.secret');

        $signature = hash_hmac('sha256', "{$consid}&{$this->timestamp}", $secret, true);

        return base64_encode($signature);
    }

    protected function decryptResponse(?string $key = null): void
    {
        $consid = config('bpjs.consid');
        $secret = config('bpjs.secret');

        $hash = hex2bin(hash('sha256', $consid . $secret . $this->timestamp));

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
    }

    /**
     * @return mixed
     */
    public function response()
    {
        return $this->response;
    }
}
