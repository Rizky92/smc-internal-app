<?php

namespace App\Http\Controllers\BPJS;

use App\Http\Controllers\Controller;
use App\Support\BPJS\BpjsService;

class MobileJKNController extends Controller
{
    public function __invoke()
    {
        $getListTask = BpjsService::start()
            ->getListTask('20230309000001')
            ->response();

        dd($getListTask);
    }
}
