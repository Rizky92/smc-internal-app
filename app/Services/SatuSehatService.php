<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;

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

    protected function importRsaKey($pem)
    {
        // Fetch the part of the PEM string between header and footer
        $pemHeader = "-----BEGIN PUBLIC KEY-----";
        $pemFooter = "-----END PUBLIC KEY-----";
        $pemContents = substr($pem, strlen($pemHeader), strlen($pem) - strlen($pemFooter));

        // Base64 decode the string to get the binary data
        $binaryDerString = base64_decode($pemContents);

        // Save the binary DER data to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'rsa_key');
        file_put_contents($tempFile, $binaryDerString);

        // Import the RSA key using phpseclib3
        $x509 = new X509();
        $key = $x509->loadX509("file://" . $tempFile)->getPublicKey();

        // Clean up the temporary file
        unlink($tempFile);

        return $key;
    }

    protected function generateSymmetricKey()
    {
        // Generate a random key using phpseclib3
        $key = Random::string(32);

        // Return the generated key
        return $key;
    }

    protected function generateRSAKeyPair()
    {
        
        $privateKey = RSA::createKey(2048);
        
        $publicKey = $privateKey->getPublicKey();
        
        $publicKey = $publicKey->toString('PKCS8');
        
        // Prepare the result
        $result = [
            'privateKey' => $privateKey,
            'publicKey' => $publicKey,
        ];

        return $result;
    }

    protected function formatMessage($data) 
    {
        $dataAsBase64 = chunk_split(base64_encode($data));
        return "-----BEGIN ENCRYPTED MESSAGE-----\r\n{$dataAsBase64}-----END ENCRYPTED MESSAGE-----";
    }

    protected function aesEncrypt($data, $symmetricKey) 
    {
        $ivLength = 12;
        $iv = random_bytes($ivLength);
        
        // $cipher = new AES(AES::MODE_GCM);
        $cipher = new AES('gcm');
        $cipher->setKeyLength(256);
        $cipher->setKey($symmetricKey);
        $cipher->setNonce($iv);

        $ciphertext = $cipher->encrypt($data);
        $tag = $cipher->getTag();
        
        // Concatenate the IV, ciphertext, and tag
        $encryptedData = $iv . $ciphertext. $tag;

        return $encryptedData;
    }

    protected function aesDecrypt($encryptedData, $symmetricKey)
    {
        $cipher = 'aes-256-gcm';
        $ivLength = 12;
        $tagLength = 16;

        // Extract IV, encrypted bytes, and tag
        $iv = substr($encryptedData, 0, $ivLength);
        $tag = substr($encryptedData, -$tagLength);
        $ciphertext = substr($encryptedData, $ivLength, -$tagLength);
        
        // Initialize AES object
        $aes = new AES('gcm');
        $aes->setKey($symmetricKey);
        $aes->setNonce($iv); // Use setNonce instead of setIV for GCM mode
        $aes->setTag($tag);

        // Decrypt the data
        $decryptedData = $aes->decrypt($ciphertext);

        return $decryptedData;
    }

    protected function encryptMessage($message, $pubPEM) 
    {    
        // Generate a symmetric key
        $aesKey = $this->generateSymmetricKey(); // Generate a 256-bit key (32 bytes)
        
        $serverKey = PublicKeyLoader::load($pubPEM);
        $serverKey = $serverKey->withPadding(RSA::ENCRYPTION_OAEP);
        $wrappedAesKey = $serverKey->encrypt($aesKey);
        
        // Encrypt the message using the generated AES key
        $encryptedMessage = $this->aesEncrypt($message, $aesKey);
        
        // Combine wrapped AES key and encrypted message
        $payload = $wrappedAesKey . $encryptedMessage;

        return $this->formatMessage($payload);
    }

//done php_sec_lib
    protected function decryptMessage($message, $privateKey)
    {
        $beginTag = "-----BEGIN ENCRYPTED MESSAGE-----";
        $endTag = "-----END ENCRYPTED MESSAGE-----";

        // Fetch the part of the PEM string between beginTag and endTag
        $messageContents = substr(
            $message,
            strlen($beginTag) + 1,
            strlen($message) - strlen($endTag) - strlen($beginTag) - 2
        );
        
        // Base64 decode the string to get the binary data
        $binaryDerString = base64_decode($messageContents);
        
        // Split the binary data into wrapped key and encrypted message
        $wrappedKeyLength = 256;
        $wrappedKey = substr($binaryDerString, 0, $wrappedKeyLength);
        $encryptedMessage = substr($binaryDerString, $wrappedKeyLength);
        
        // Unwrap the key using RSA private key
        $key = PublicKeyLoader::load($privateKey);
        $aesKey = $key->decrypt($wrappedKey);

        // Decrypt the encrypted message using the unwrapped key
        $decryptedMessage = $this->aesDecrypt($encryptedMessage, $aesKey);

        return $decryptedMessage;
    }

    public function generateKycUrl($agentName, $agentNik)
    {
        $this->authenticate();

        $environtment = config('satusehat.environment');

        $keyPair = $this->generateRSAKeyPair();
        $publicKey = $keyPair['publicKey'];
        $privateKey = $keyPair['privateKey'];

        if ($environtment == 'development') {
            $pubMem = <<<EOD
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqoicEXIYWYV3PvLIdvB
            qFkHn2IMhPGKTiB2XA56enpPb0UbI9oHoetRF41vfwMqfFsy5Yd5LABxMGyHJBbP
            +3fk2/PIfv+7+9/dKK7h1CaRTeT4lzJBiUM81hkCFlZjVFyHUFtaNfvQeO2OYb7U
            kK5JrdrB4sgf50gHikeDsyFUZD1o5JspdlfqDjANYAhfz3aam7kCjfYvjgneqkV8
            pZDVqJpQA3MHAWBjGEJ+R8y03hs0aafWRfFG9AcyaA5Ct5waUOKHWWV9sv5DQXmb
            EAoqcx0ZPzmHJDQYlihPW4FIvb93fMik+eW8eZF3A920DzuuFucpblWU9J9o5w+2
            oQIDAQAB
            -----END PUBLIC KEY-----
            EOD;
        } elseif ($environtment == 'production') {
            $pubPEM = <<<EOD
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxLwvebfOrPLIODIxAwFp
            4Qhksdtn7bEby5OhkQNLTdClGAbTe2tOO5Tiib9pcdruKxTodo481iGXTHR5033I
            A5X55PegFeoY95NH5Noj6UUhyTFfRuwnhtGJgv9buTeBa4pLgHakfebqzKXr0Lce
            /Ff1MnmQAdJTlvpOdVWJggsb26fD3cXyxQsbgtQYntmek2qvex/gPM9Nqa5qYrXx
            8KuGuqHIFQa5t7UUH8WcxlLVRHWOtEQ3+Y6TQr8sIpSVszfhpjh9+Cag1EgaMzk+
            HhAxMtXZgpyHffGHmPJ9eXbBO008tUzrE88fcuJ5pMF0LATO6ayXTKgZVU0WO/4e
            iQIDAQAB
            -----END PUBLIC KEY-----
            EOD;
        } else {
            throw new \Exception('Unknown environment for SatuSehat KYC');
        }

        $data = [
            'agent_name' => $agentName,
            'agent_nik' => $agentNik,
            'public_key' => $publicKey,
        ];

        $jsonData = json_encode($data);
        $encryptedPayload = $this->encryptMessage($jsonData, $pubPEM);

        $response = Http::withToken($this->token)
            ->withOptions(['verify' => false]) // Disable SSL verification comment this line in production
            ->withHeaders([
                'X-Debug-Mode' => '0',
                'Content-Type' => 'text/plain'
            ])
            ->withBody($encryptedPayload, 'text/plain')
            ->post(config('satusehat.api_url'));

        if ($response->failed()) {
            throw new \Exception("HTTP Error: " . $response->status() . " - " . $response->body());
        }

        $encryptedResponse = $response->body();
        return $this->decryptMessage($encryptedResponse, $privateKey);
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
