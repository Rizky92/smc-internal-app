<?php

namespace App\Models\RekamMedis;

use Illuminate\Database\Eloquent\Model;

class DemografiPasien extends Model
{
    protected $connection = 'mysql_smc';

    protected $primaryKey = 'no_rawat';

    protected $keyType = 'string';

    protected $table = 'demografi_pasien';

    public $incrementing = false;

    public $timestamps = false;
}
