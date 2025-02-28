<?php

namespace App\Models\Keuangan\Jurnal;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JurnalMedis extends Model
{
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
            $tglAwal = now()->startOfMonth()->toDateString();
        }

        if (empty($tglAkhir)) {
            $tglAkhir = now()->endOfMonth()->toDateString();
        }

        $db = DB::connection('mysql_sik')->getDatabaseName();

        $sqlSelect = <<<'SQL'
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

        $this->addSearchConditions([
            'jurnal_medis.id',
            'jurnal_medis.no_jurnal',
            'jurnal_medis.waktu_jurnal',
            'jurnal_medis.no_faktur',
            'jurnal_medis.ket',
            'jurnal_medis.status',
            'bayar_pemesanan.besar_bayar',
            'bayar_pemesanan.nama_bayar',
            'rekening.kd_rek',
            'rekening.nm_rek',
            'datasuplier.nama_suplier',
            'jurnal_medis.nik',
            'pegawai.nama',
        ]);

        $this->addRawColumns('nm_pegawai', DB::raw("trim(concat(jurnal_medis.nik, ' ', coalesce(pegawai.nama, '')))"));

        return $query
            ->selectRaw($sqlSelect)
            ->withCasts(['besar_bayar' => 'float'])
            ->join(DB::raw("{$db}.bayar_pemesanan bayar_pemesanan"), 'jurnal_medis.no_faktur', '=', 'bayar_pemesanan.no_faktur')
            ->leftJoin(DB::raw("{$db}.pemesanan pemesanan"), 'jurnal_medis.no_faktur', '=', 'pemesanan.no_faktur')
            ->leftJoin(DB::raw("{$db}.datasuplier datasuplier"), 'pemesanan.kode_suplier', '=', 'datasuplier.kode_suplier')
            ->leftJoin(DB::raw("{$db}.akun_bayar_hutang akun_bayar_hutang"), 'bayar_pemesanan.nama_bayar', '=', 'akun_bayar_hutang.nama_bayar')
            ->leftJoin(DB::raw("{$db}.rekening rekening"), 'akun_bayar_hutang.kd_rek', '=', 'rekening.kd_rek')
            ->leftJoin(DB::raw("{$db}.pegawai pegawai"), 'jurnal_medis.nik', '=', 'pegawai.nik')
            ->whereBetween(DB::raw('date(jurnal_medis.waktu_jurnal)'), [$tglAwal, $tglAkhir]);
    }

    public static function refreshModel(): void
    {
        $latest = static::latest('waktu_jurnal')->value('waktu_jurnal');

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->when(
                ! is_null($latest),
                fn (QueryBuilder $query) => $query->whereRaw('timestamp(tgl_jurnal, jam_jurnal) > ?', $latest),
                fn (QueryBuilder $query) => $query->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where(fn (QueryBuilder $q) => $q
                ->where('keterangan', 'like', 'BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR %%, OLEH %')
                ->orWhere('keterangan', 'like', 'BATAL BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR %%, OLEH %'))
            ->orderBy('no_jurnal')
            ->chunk(500, function (Collection $jurnal) {
                $data = $jurnal->map(function (object $value): array {
                    /** @var object{no_jurnal: string, no_bukti: string, tgl_jurnal: string, jam_jurnal: string, jenis: "U"|"P", keterangan: string} $value */
                    $ket = str($value->keterangan);

                    $status = $ket->startsWith('BATAL');
                    $noFaktur = $ket->after('NO.FAKTUR ')->beforeLast(',')->trim()->value();
                    $petugas = $ket->after('OLEH ')->trim()->value();

                    return [
                        'no_jurnal'    => $value->no_jurnal,
                        'waktu_jurnal' => "{$value->tgl_jurnal} {$value->jam_jurnal}",
                        'no_faktur'    => $noFaktur,
                        'status'       => $status ? 'Batal' : 'Sudah',
                        'ket'          => $value->keterangan,
                        'nik'          => $petugas,
                    ];
                });

                static::insert($data->all());
            });
    }
}
