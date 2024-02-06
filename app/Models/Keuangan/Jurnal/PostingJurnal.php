<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;

class PostingJurnal extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'posting_jurnal';

    protected $fillable = [
        'no_jurnal',
        'tgl_jurnal',
    ];
}
