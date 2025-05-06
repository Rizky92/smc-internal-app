<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SatuSehatService
{
    protected $client;

    protected $secret;

    protected $environment;

    protected $token;

    protected $issuedAt;

    public function __construct(array $config = [])
    {
        $this->client = $config['client'] ?? config('satusehat.client');
        $this->secret = $config['secret'] ?? config('satusehat.secret');
    }

    public static function create(): void {}

    protected function authenticate(): void
    {
        $response = Http::asForm()
            ->withOptions(['verify' => false])
            ->post(config('satusehat.auth_url'), [
                'grant_type'    => 'client_credentials', // penting! biasanya perlu grant_type ini
                'client_id'     => $this->client,
                'client_secret' => $this->secret,
            ])
            ->throw()
            ->json();
    
        $this->token = $response['access_token'] ?? null;
        $this->issuedAt = now();
    }

    public function cekPasienByNikNameBirthDate(string $name, string $birthDate, string $nik): array
    {
        if (empty($this->token)) {
            $this->authenticate();
        }
    
        $url = config('satusehat.fhir_url') . '/Patient';
        $response = Http::withToken($this->token)
            ->withOptions([
                'verify' => false,
            ])
            ->get($url, [
                'name' => $name,
                'birthdate' => $birthDate,
                'identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik,
            ]);
    
        if (! $response->ok()) {
            throw new \Exception('Gagal mendapatkan data pasien: ' . $response->body());
        }
    
        return $response->json();
    }
}
