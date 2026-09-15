<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kredensial Webhook
    |--------------------------------------------------------------------------
    |
    | SATUSEHAT DICOM Router mengirim hasil pengiriman DICOM ke endpoint
    | `POST /api/webhook/dicom-router` memakai HTTP Basic Auth. Value-nya
    | diambil dari `webhook_user` dan `webhook_password` pada `router.conf`
    | milik router (atau env WEBHOOK_USER / WEBHOOK_PASSWORD di container-nya).
    |
    | Router tidak menandatangani request dan tidak punya mekanisme retry,
    | jadi Basic Auth adalah satu-satunya pengaman endpoint ini. Kosongkan
    | keduanya hanya jika endpoint sengaja dibuka tanpa autentikasi.
    |
    */

    'webhook' => [
        'username' => env('DICOM_ROUTER_WEBHOOK_USER'),
        'password' => env('DICOM_ROUTER_WEBHOOK_PASSWORD'),
    ],

];
