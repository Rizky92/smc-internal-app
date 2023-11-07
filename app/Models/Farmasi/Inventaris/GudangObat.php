<?php

namespace App\Models\Farmasi\Inventaris;

use App\Database\Eloquent\Model;
use App\Models\Bangsal;
use App\Models\Farmasi\Obat;
use App\Models\Farmasi\PemberianObat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GudangObat extends Model
{
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

        $this->addSearchConditions([
            'bangsal.nm_bangsal',
            'gudangbarang.kode_brng',
            'databarang.nama_brng',
            'kodesatuan.satuan',
            'kodesatuan.kode_sat',
        ]);

        $this->addSortColumns([
            'projeksi_harga' => DB::raw('round(databarang.h_beli * if(gudangbarang.stok < 0, 0, gudangbarang.stok))')
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts([
                'stok' => 'float',
                'h_beli' => 'float',
                'projeksi_harga' => 'float',
            ])
            ->leftJoin('databarang', 'gudangbarang.kode_brng', '=', 'databarang.kode_brng')
            ->leftJoin('kodesatuan', 'databarang.kode_sat', '=', 'kodesatuan.kode_sat')
            ->leftJoin('bangsal', 'gudangbarang.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->when($kodeBangsal !== '-', fn (Builder $query) => $query->where('gudangbarang.kd_bangsal', $kodeBangsal));
    }
    
    public function scopeDefectaDepo(Builder $query, string $tanggal, string $shift, string $bangsal): Builder
    {
        $tanggal = carbon_immutable($tanggal);

        $waktuShift = Cache::remember('waktu_shift', now()->addDay(), function () use ($shift) {
            return DB::connection('mysql_sik')
                ->table('closing_kasir')
                ->where('shift', $shift)
                ->first(['jam_masuk', 'jam_pulang']);
        });

        $pemberianObat = PemberianObat::query()
            ->selectRaw(<<<SQL
                kd_bangsal,
                kode_brng,
                sum(jml) as jumlah
            SQL)
            ->whereIn('kd_bangsa', ['IFA', 'IFG', 'IFI'])
            ->groupByRaw('kd_bangsal, kode_brng');

        $waktuAwalShift = $tanggal->setTimeFromTimeString($waktuShift->jam_masuk);
        $waktuAkhirShift = $tanggal->setTimeFromTimeString($waktuShift->jam_pulang);

        if ($shift === 'Malam') {
            $waktuAkhirShift = $waktuAkhirShift->addDay();
        }

        $pemberianObatPerShift = $pemberianObat
            ->whereBetween(
                DB::raw("cast(concat(tgl_perawatan, ' ', jam) as datetime)"),
                [$waktuAwalShift->toDateTimeString(), $waktuAkhirShift->toDateTimeString()]
            );

        $pemberianObat3Hari = $pemberianObat
            ->whereBetween('tgl_perawatan', [$tanggal->subDays(3)->toDateString(), $tanggal->toDateString()]);

        $sqlSelect = <<<SQL
            gudangbarang.kode_brng,
            databarang.nama_brng,
            kodesatuan.satuan,
            gudangbarang.stok,
            pemberian_obat_shift.jumlah jumlah_shift,
            pemberian_obat_3hari.jumlah jumlah_3hari,
        SQL;

        $this->addSearchConditions([
            'gudangbarang.kode_brng',
            'databarang.nama_brng',
            'databarang.kode_sat',
            'kodesatuan.satuan',
        ]);

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['jumlah_shift' => 'float', 'jumlah_3hari' => 'float'])
            ->leftJoinSub($pemberianObatPerShift, 'pemberian_obat_shift', fn (JoinClause $join) => $join
                ->on('gudangbarang.kode_brng', '=', 'pemberian_obat_shift.kode_brng')
                ->on('gudangbarang.kd_bangsal', '=', 'pemberian_obat_shift.kd_bangsal'))
            ->leftJoinSub($pemberianObat3Hari, 'pemberian_obat_3hari', fn (JoinClause $join) => $join
                ->on('gudangbarang.kode_brng', '=', 'pemberian_obat_3hari.kode_brng')
                ->on('gudangbarang.kd_bangsal', '=', 'pemberian_obat_3hari.kd_bangsal'))
            ->where('gudangbarang.kd_bangsal', $bangsal);
    }
}
