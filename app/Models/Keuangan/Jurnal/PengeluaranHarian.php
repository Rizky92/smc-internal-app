<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Eloquent\Concerns\Searchable;
use App\Support\Eloquent\Concerns\Sortable;
use App\Support\Eloquent\Model;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LogicException;

class PengeluaranHarian extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_sik';

    protected $table = 'pengeluaran_harian';

    protected $primaryKey = 'no_keluar';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        // 'no_keluar',
        // 'tanggal',
        // 'kode_kategori',
        // 'biaya',
        // 'nip',
        // 'keterangan',
    ];

}
