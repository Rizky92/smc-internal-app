<?php

namespace App\Models\Keuangan;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiutangPasienDetail extends Model
{
    protected $connection = 'mysql_sik';

    protected $table = 'detail_piutang_pasien';

    protected $primaryKey = null;

    protected $keyType = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $perPage = 25;

    protected $fillable = [
        'no_rawat',
        'nama_bayar',
        'kd_pj',
        'totalpiutang',
        'sisapiutang',
        'tgltempo',
    ];

    protected $casts = [
        'totalpiutang' => 'float',
        'sisapiutang'  => 'float',
    ];

    protected $searchColumns = [
        'no_rawat',
        'nama_bayar',
        'kd_pj',
        'totalpiutang',
        'sisapiutang',
        'tgltempo',
    ];

    public function piutangPasien(): BelongsTo
    {
        return $this->belongsTo(PiutangPasien::class, 'no_rawat', 'no_rawat');
    }
}
