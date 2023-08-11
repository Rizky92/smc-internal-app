<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiutangPasienDetail extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $table = 'detail_piutang_pasien';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

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
        'sisapiutang' => 'float',
    ];

    protected array $searchColumns = [
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
