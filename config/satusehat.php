<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Atur environment untuk terhubung ke Satu Sehat.
    |
    */

    'environment' => env('SATUSEHAT_ENV', 'development'),

    /*
    |--------------------------------------------------------------------------
    | Authentication URL Satu Sehat
    |--------------------------------------------------------------------------
    */

    'auth_url' => env('SATUSEHAT_AUTH_URL'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | URL untuk terhubung ke Satu Sehat.
    */

    'fhir_url' => env('SATUSEHAT_FHIR_URL'),

    /*
    |--------------------------------------------------------------------------
    | Organization ID
    |--------------------------------------------------------------------------
    |
    | @TODO untuk saat ini belum digunakan
    |
    */

    'organization_id' => env('SATUSEHAT_ORG_ID'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Atur client key dan secret key. Diharap **UNTUK TIDAK HARDCODE VALUENYA DISINI**.
    | Simpan value client dan secret di file .env
    |
    */

    'client' => env('SATUSEHAT_CLIENT'),
    'secret' => env('SATUSEHAT_SECRET'),
];
