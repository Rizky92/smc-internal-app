<?php

namespace App\Models;

use App\Models\Perawatan\Kamar;
use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bangsal extends Model
{
    protected $connection = 'mysql_sik';

    protected $primaryKey = 'kd_bangsal';

    protected $keyType = 'string';

    protected $table = 'bangsal';

    public $incrementing = false;

    public $timestamps = false;

    public function mappingBidang(): BelongsToMany
    {
        return $this->belongsToMany(Bidang::class, 'mapping_bidang', 'bidang_id', 'kd_bangsal', 'id', 'kd_bangsal');
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function scopeInformasiKamar(Builder $query): Builder
    {
        $sqlSelect = <<<SQL
            bangsal.nm_bangsal,
            kamar.kelas,
            SUM(kamar.status = 'ISI') as total_terisi,
            SUM(kamar.status = 'KOSONG') as total_tersedia
        SQL;

        $this->addSearchConditions([
            'bangsal.nm_bangsal',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->join('kamar', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
            ->groupBy('bangsal.nm_bangsal', 'kamar.kelas')
            ->where('bangsal.status', '=', '1');
    }

}
