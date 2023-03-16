<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class JurnalRiwayatPerbaikan extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $table = 'jurnal_riwayat_perbaikan';
}
