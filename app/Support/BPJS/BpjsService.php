<?php

namespace App\Support\BPJS;

use Illuminate\Http\Response;

class BpjsService
{
    protected $timestamp = '';

    public static function getListTask(string $noBooking): Response
    {
        $this->timestamp = now()->format('U');
    }

    protected function generateSignature()
    {
        
    }

    protected function encrypt()
    {

    }

    protected function decrypt()
}