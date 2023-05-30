<?php

namespace App\Http\Controllers\BPJS;

use App\Support\BPJS\BpjsService;

class MobileJKNController
{
    /** 
     * @return never
     */
    public function __invoke()
    {
        $getListTask = BpjsService::start()
            ->getListTask('20230309000001')
            ->response();

        dd($getListTask);
    }
}
