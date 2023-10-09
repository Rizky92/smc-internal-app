<?php

namespace App\Models\Aplikasi;

use App\Casts\BooleanCast;
use App\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HakAkses extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'khanza_mapping_akses';

    protected $primaryKey = 'nama_field';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'nama_field',
        'judul_menu',
        'default_value',
    ];

    protected $casts = [
        'default_value' => BooleanCast::class,
    ];

    protected function searchColumns(): array
    {
        return [
            'nama_field',
            DB::raw('ifnull(judul_menu, "")'),
        ];
    }
}
