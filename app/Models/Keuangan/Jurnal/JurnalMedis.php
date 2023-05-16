<?php

namespace App\Models\Keuangan\Jurnal;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JurnalMedis extends Model
{
    use Sortable, Searchable;
    
    protected $connection = 'mysql_smc';

    protected $table = 'jurnal_medis';

    public $timestamps = false;

    protected $fillable = [
        'no_jurnal',
        'waktu_jurnal',
        'no_faktur',
        'status',
        'ket',
        'nik',
    ];

    public function scopeJurnalPenerimaanBarang(Builder $query, string $tglAwal = '', string $tglAkhir = ''): Builder
    {
        if (empty($tglAwal)) {
            $tglAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        $db = DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<SQL
            jurnal_medis.id,
            jurnal_medis.no_jurnal,
            jurnal_medis.waktu_jurnal,
            jurnal_medis.no_faktur,
            jurnal_medis.ket,
            jurnal_medis.status,
            bayar_pemesanan.besar_bayar,
            bayar_pemesanan.nama_bayar,
            rekening.kd_rek,
            rekening.nm_rek,
            datasuplier.nama_suplier,
            trim(concat(jurnal_medis.nik, ' ', coalesce(pegawai.nama, ''))) nm_pegawai
        SQL;

        return $query
            ->selectRaw($sqlSelect)
            ->join(DB::raw("{$db}.bayar_pemesanan bayar_pemesanan"), 'jurnal_medis.no_faktur', '=', 'bayar_pemesanan.no_faktur')
            ->leftJoin(DB::raw("{$db}.pemesanan pemesanan"), 'jurnal_medis.no_faktur', '=', 'pemesanan.no_faktur')
            ->leftJoin(DB::raw("{$db}.datasuplier datasuplier"), 'pemesanan.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->leftJoin(DB::raw("{$db}.akun_bayar_hutang akun_bayar_hutang"), 'bayar_pemesanan.nama_bayar', '=', 'akun_bayar_hutang.nama_bayar')
            ->leftJoin(DB::raw("{$db}.rekening rekening"), 'akun_bayar_hutang.kd_rek', '=', 'rekening.kd_rek')
            ->leftJoin(DB::raw("{$db}.pegawai pegawai"), 'jurnal_medis.nik', '=', 'pegawai.nik')
            ->whereBetween(DB::raw('date(jurnal_medis.waktu_jurnal)'), [$tglAwal, $tglAkhir]);
    }

    public static function refreshModel()
    {
        $latest = static::latest('waktu_jurnal')->first();

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->when(
                !is_null($latest),
                fn ($query) => $query->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->waktu_jurnal),
                fn ($query) => $query->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where('keterangan', 'like', '%BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR %%, OLEH %')
            ->where('keterangan', 'not like', '%adjustmen%')
            ->orderBy('no_jurnal')
            ->chunk(500, function ($jurnal) {
                /** @var \Illuminate\Support\Collection $jurnal */

                $data = $jurnal->map(function ($value, $key) {
                    $ket = Str::of($value->keterangan);

                    $status = $ket->startsWith('BATAL');
                    $noFaktur = (string) $ket->after('NO.FAKTUR ')->beforeLast(',')->trim();
                    $petugas = (string) $ket->after('OLEH ')->trim();

                    return [
                        'no_jurnal' => $value->no_jurnal,
                        'waktu_jurnal' => "{$value->tgl_jurnal} {$value->jam_jurnal}",
                        'no_faktur' => $noFaktur,
                        'status' => $status ? 'Batal' : 'Sudah',
                        'ket' => $value->keterangan,
                        'nik' => $petugas,
                    ];
                });

                static::insert($data->all());
            });
    }
}
