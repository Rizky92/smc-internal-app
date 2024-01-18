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

    protected function authenticate()
    {
        $response = Http::asForm()
            ->post('https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1/accesstoken?grant_type=client_credentials', [
                'client_id'     => $this->client,
                'client_secret' => $this->secret,
            ])
            ->collect();
    }
}
