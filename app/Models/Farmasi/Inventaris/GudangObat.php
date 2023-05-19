<?php

namespace App\Models\Farmasi\Inventaris;

use App\Models\Bangsal;
use App\Models\Farmasi\Obat;
use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GudangObat extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_sik';
    
    protected $primaryKey = false;

    protected $keyType = null;

    protected $table = 'gudangbarang';

    public $incrementing = false;

    public $timestamps = false;

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'kode_brng', 'kode_brng');
    }

    public function bangsal(): BelongsTo
    {
        return $this->belongsTo(Bangsal::class, 'kd_bangsal', 'kd_bangsal');
    }

    public function scopeBangsalYangAda(Builder $query): Builder
    {
        return $query
            ->selectRaw("distinct(gudangbarang.kd_bangsal) kd_bangsal, bangsal.nm_bangsal")
            ->join('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal');
    }

    public function scopeStokPerRuangan(Builder $query, string $kodeBangsal = '-'): Builder
    {
        $sqlSelect = <<<SQL
            bangsal.nm_bangsal,
            gudangbarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan,
            gudangbarang.stok,
            databarang.h_beli,
            round(databarang.h_beli * if(gudangbarang.stok < 0, 0, gudangbarang.stok)) projeksi_harga
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin('databarang', 'gudangbarang.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->when($kodeBangsal !== '-', fn (Builder $query) => $query->where('gudangbarang.kd_bangsal', $kodeBangsal));
    }
}
