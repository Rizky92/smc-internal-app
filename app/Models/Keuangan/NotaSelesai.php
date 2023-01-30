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
        'status_pasien',
        'bentuk_bayar',
        'user_id',
    ];

    public function scopeBillingYangDiselesaikan(Builder $query, string $periodeAwal = '', string $periodeAkhir = ''): Builder
    {
        if (empty($periodeAwal)) {
            $periodeAwal = now()->format('Y-m-d');
        }

        if (empty($periodeAkhir)) {
            $periodeAkhir = now()->addDay()->format('Y-m-d');
        }

        $database = DB::connection('mysql_sik')->getDatabaseName();

        $notaPasien = DB::raw("(
            select
                nota_jalan.no_rawat,
                nota_jalan.no_nota,
                timestamp(nota_jalan.tanggal, nota_jalan.jam) waktu,
                detail_nota_jalan.nama_bayar,
                sum(detail_nota_jalan.besar_bayar) besar_bayar
            from {$database}.nota_jalan nota_jalan
            join {$database}.detail_nota_jalan detail_nota_jalan on nota_jalan.no_rawat = detail_nota_jalan.no_rawat
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
                (sum(detail_nota_inap.besar_bayar) - nota_inap.uang_muka) besar_bayar
            from {$database}.nota_inap nota_inap
            join {$database}.detail_nota_inap detail_nota_inap on nota_inap.no_rawat = detail_nota_inap.no_rawat
            group by
                nota_inap.no_rawat,
                nota_inap.no_nota,
                timestamp(nota_inap.tanggal, nota_inap.jam),
                detail_nota_inap.nama_bayar
        ) nota_pasien");

        return $query
            ->selectRaw("
                nota_selesai.no_rawat,
                pasien.no_rkm_medis,
                trim(pasien.nm_pasien) nm_pasien,
                nota_pasien.no_nota,
                ifnull(concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal), '-') ruangan,
                nota_selesai.status_pasien,
                nota_selesai.bentuk_bayar,
                nota_pasien.besar_bayar,
                penjab.png_jawab,
                nota_selesai.tgl_penyelesaian,
                concat(nota_selesai.user_id, ' ', pegawai.nama) nama_pegawai
            ")
            ->leftJoin(DB::raw($database . '.reg_periksa reg_periksa'), 'nota_selesai.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin(DB::raw($database . '.kamar_inap kamar_inap'), 'nota_selesai.no_rawat', '=', 'kamar_inap.no_rawat')
            ->leftJoin($notaPasien, 'nota_selesai.no_rawat', '=', 'nota_pasien.no_rawat')
            ->leftJoin(DB::raw($database . '.penjab penjab'), 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin(DB::raw($database . '.kamar kamar'), 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->leftJoin(DB::raw($database . '.bangsal bangsal'), 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->leftJoin(DB::raw($database . '.pasien pasien'), 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin(DB::raw($database . '.pegawai'), 'nota_selesai.user_id', '=', 'pegawai.nik')
            ->whereBetween('nota_selesai.tgl_penyelesaian', [$periodeAwal, $periodeAkhir])
            ->groupByRaw("
                nota_selesai.no_rawat,
                nota_selesai.status_pasien,
                nota_selesai.bentuk_bayar,
                nota_selesai.tgl_penyelesaian
            ");
    }

    public static function refreshModel()
    {
        $latest = static::latest('tgl_penyelesaian')->first();

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->when(
                !is_null($latest),
                fn ($query) => $query->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->tgl_penyelesaian)
            )
            ->where(fn ($query) => $query
                ->where('keterangan', 'like', '%PEMBAYARAN PASIEN RAWAT JALAN% %DIPOSTING OLEH%')
                ->orWhere('keterangan', 'like', '%PEMBAYARAN PASIEN RAWAT INAP% %DIPOSTING OLEH%')
                ->orWhere('keterangan', 'like', '%PIUTANG PASIEN RAWAT RALAN% %DIPOSTING OLEH%')
                ->orWhere('keterangan', 'like', '%PIUTANG PASIEN RAWAT INAP% %DIPOSTING OLEH%'))
            ->orderBy('no_jurnal')
            ->chunk(500, function ($jurnal) {
                $data = $jurnal->map(function ($value, $key) {
                    $ket = Str::of($value->keterangan);

                    $bentukBayar = $ket->before('PASIEN')->words(1, '')->trim();
                    $statusPasien = $ket->after('PASIEN')->words(2, '')->trim();

                    $noRawat = $ket->matchAll('/\d+/')->take(4)->join('/');
                    $petugas = $ket->matchAll('/\d+/')->last();

                    return [
                        'no_rawat' => $noRawat,
                        'tgl_penyelesaian' => "{$value->tgl_jurnal} {$value->jam_jurnal}",
                        'status_pasien' => $statusPasien,
                        'bentuk_bayar' => $bentukBayar,
                        'user_id' => $petugas,
                    ];
                });

                static::insert($data->all());
            });
    }
}
