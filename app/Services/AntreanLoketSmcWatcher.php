<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AntreanLoketSmcWatcher
{
    public function current(): ?object
    {
        return DB::connection('mysql_sik')->table('antriloketsmc')->first();
    }
}
