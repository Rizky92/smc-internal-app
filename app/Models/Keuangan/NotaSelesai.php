<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotaSelesai extends Model
{
    use Searchable, Sortable;

    protected $connection = 'mysql_smc';

    protected $table = 'nota_selesai';

    public $timestamps = false;

    protected $fillable = [
        'no_rawat',
        'tgl_penyelesaian',
        'bentuk_bayar',
        'user_id',
    ];

    public function scopeBillingYangDiselesaikan(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->addDay()->format('Y-m-d');
        }

        $sik = DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<SQL
            nota_selesai.id,
            nota_selesai.no_rawat,
            pasien.no_rkm_medis,
            trim(pasien.nm_pasien) nm_pasien,
            nota_pasien.no_nota,
            ifnull(concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal), '-') ruangan,
            nota_selesai.status_pasien,
            nota_selesai.bentuk_bayar,
            coalesce(nota_pasien.besar_bayar, piutang_pasien.totalpiutang) besar_bayar,
            penjab.png_jawab,
            nota_selesai.tgl_penyelesaian,
            concat(nota_selesai.user_id, ' ', pegawai.nama) nama_pegawai
        SQL;

        $notaPasien = DB::raw("(
            select
                nota_jalan.no_rawat,
                nota_jalan.no_nota,
                timestamp(nota_jalan.tanggal, nota_jalan.jam) waktu,
                detail_nota_jalan.nama_bayar,
                sum(detail_nota_jalan.besar_bayar) besar_bayar
            from {$sik}.nota_jalan nota_jalan
            join {$sik}.detail_nota_jalan detail_nota_jalan on nota_jalan.no_rawat = detail_nota_jalan.no_rawat
            group by 
                nota_jalan.no_rawat,
                nota_jalan.no_nota,
                timestamp(nota_jalan.tanggal, nota_jalan.jam),
                detail_nota_jalan.nama_bayar
            union all
            select
                nota_inap.no_rawat,
                nota_inap.no_nota,
                timestamp(nota_inap.tanggal, nota_inap.jam) waktu,
                detail_nota_inap.nama_bayar,
                (sum(detail_nota_inap.besar_bayar) + nota_inap.uang_muka) besar_bayar
            from {$sik}.nota_inap nota_inap
            join {$sik}.detail_nota_inap detail_nota_inap on nota_inap.no_rawat = detail_nota_inap.no_rawat
            group by
                nota_inap.no_rawat,
                nota_inap.no_nota,
                timestamp(nota_inap.tanggal, nota_inap.jam),
                detail_nota_inap.nama_bayar
        ) nota_pasien");

        return $query
            ->selectRaw($sqlSelect)
            ->leftJoin(DB::raw($sik . '.reg_periksa reg_periksa'), 'nota_selesai.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin(DB::raw($sik . '.kamar_inap kamar_inap'), 'nota_selesai.no_rawat', '=', 'kamar_inap.no_rawat')
            ->leftJoin(DB::raw($sik . '.piutang_pasien piutang_pasien'), 'nota_selesai.no_rawat', '=', 'piutang_pasien.no_rawat')
            ->leftJoin($notaPasien, 'nota_selesai.no_rawat', '=', 'nota_pasien.no_rawat')
            ->leftJoin(DB::raw($sik . '.penjab penjab'), 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin(DB::raw($sik . '.kamar kamar'), 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->leftJoin(DB::raw($sik . '.bangsal bangsal'), 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->leftJoin(DB::raw($sik . '.pasien pasien'), 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin(DB::raw($sik . '.pegawai'), 'nota_selesai.user_id', '=', 'pegawai.nik')
            ->whereBetween(DB::raw("date(nota_selesai.tgl_penyelesaian)"), [$tglAwal, $tglAkhir])
            ->groupByRaw("
                nota_selesai.no_rawat,
                nota_selesai.status_pasien,
                nota_selesai.bentuk_bayar,
                nota_selesai.tgl_penyelesaian
            ");
    }

    public static function refreshModel(): void
    {
        $latest = static::latest('tgl_penyelesaian')->first();

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->when(
                !is_null($latest),
                fn ($query) => $query->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->tgl_penyelesaian),
                fn ($query) => $query->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where(fn ($query) => $query
                ->where('keterangan', 'like', '%PEMBAYARAN PASIEN RAWAT JALAN% %DIPOSTING OLEH%')
                ->orWhere('keterangan', 'like', '%PEMBAYARAN PASIEN RAWAT INAP% %DIPOSTING OLEH%')
                ->orWhere('keterangan', 'like', '%PIUTANG PASIEN RAWAT JALAN% %DIPOSTING OLEH%')
                ->orWhere('keterangan', 'like', '%PIUTANG PASIEN RAWAT INAP% %DIPOSTING OLEH%'))
            ->orderBy('no_jurnal')
            ->chunk(500, function ($jurnal) {
                /** @var \Illuminate\Support\Collection $jurnal */

                $data = $jurnal->map(function ($value, $key) {
                    $ket = Str::of($value->keterangan);

                    $bentukBayar = $ket->before('PASIEN')->words(1, '')->trim();
                    $statusPasien = $ket->after('PASIEN')->words(2, '')->trim();

                    $noRawat = $ket->matchAll('/\d+/')->take(4)->join('/');
                    $petugas = $ket->matchAll('/\d+/')->last();

                    return [
                        'no_rawat' => $noRawat,
                        'tgl_penyelesaian' => "{$value->tgl_jurnal} {$value->jam_jurnal}",
                        'bentuk_bayar' => $bentukBayar,
                        'status_pasien' => $statusPasien,
                        'user_id' => $petugas,
                    ];
                });

                static::insert($data->all());
            });
    }
}
