<?php

namespace App\Models\Farmasi;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;

class RiwayatBarangMedis extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $table = 'riwayat_barang_medis';

    protected $primaryKey = null;

    protected $keyType = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $perPage = 25;

    protected $fillable = [
        // 
    ];

    protected $casts = [
        // 
    ];

    protected $searchColumns = [
        // 
    ];
}
