<?php

namespace App\Http\Controllers\BPJS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VClaimController extends Controller
{
    public function store()
    {
        $url = "https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev";
    }
}
