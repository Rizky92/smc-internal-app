<?php

namespace App\Models\Perawatan;

use Illuminate\Database\Eloquent\Model;

class DiagnosaPasien extends Model
{
    protected $connection = 'mysql_sik';
    
    protected $table = 'diagnosa_pasien';
}
