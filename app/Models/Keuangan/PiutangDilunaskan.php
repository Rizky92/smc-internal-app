<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PiutangDilunaskan extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $table = 'piutang_dilunaskan';

    public static function refreshModel()
    {
        $latest = static::latest('waktu_jurnal')->value('waktu_jurnal');

        $sqlSelect = <<<SQL
            jurnal.no_jurnal,
            timestamp(jurnal.tgl_jurnal, jurnal.jam_jurnal) waktu_jurnal,
            detail_penagihan_piutang.no_rawat,
            penagihan_piutang.no_tagihan,
            penagihan_piutang.kd_pj,
            bayar_piutang.besar_cicilan,
            penagihan_piutang.tanggal tgl_penagihan,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            bayar_piutang.tgl_bayar,
            penagihan_piutang.kd_rek,
            akun_penagihan_piutang.nama_bank,
            jurnal.keterangan
        SQL;

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->selectRaw($sqlSelect)
            ->leftJoin('detail_penagihan_piutang', 'jurnal.no_bukti', '=', 'detail_penagihan_piutang.no_rawat')
            ->leftJoin('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->join(DB::raw('pegawai pegawai_penagih'), 'penagihan_piutang.nip', '=', 'pegawai_penagih.nik')
            ->join(DB::raw('pegawai pegawai_menyetujui'), 'penagihan_piutang.nip_menyetujui', '=', 'pegawai_menyetujui.nik')
            ->join('penjab', 'penagihan_piutang.kd_pj', '=', 'penjab.kd_pj')
            ->join('akun_penagihan_piutang', 'penagihan_piutang.kd_rek', '=', 'akun_penagihan_piutang.kd_rek')
            ->leftJoin('bayar_piutang', fn (JoinClause $join) => $join
                ->on('detail_penagihan_piutang.no_rawat', '=', 'bayar_piutang.no_rawat')
                ->on('akun_penagihan_piutang.kd_rek', '=', 'bayar_piutang.kd_rek'))
            ->when(
                !is_null($latest),
                fn ($q) => $q->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->waktu_jurnal),
                fn ($q) => $q->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where('penagihan_piutang.status', 'Sudah Dibayar')
            ->where('akun_penagihan_piutang.kd_rek', '112010')
            ->where(fn ($q) => $q
                ->where('keterangan', 'like', '%bayar piutang, oleh%')
                ->orWhere('keterangan', 'like', '%pembatalan bayar piutang, oleh%'))
            ->orderBy('jurnal.tgl_jurnal')
            ->orderBy('jurnal.jam_jurnal')
            ->chunk(500, function (Collection $collection) {
                $data = $collection->map(function ($jurnal, $key) {
                    $ket = Str::of($jurnal->keterangan);

                    $status = $ket->startsWith('PEMBATALAN');

                    return [
                        'no_jurnal' => $jurnal->no_jurnal,
                        'waktu_jurnal' => $jurnal->waktu_jurnal,
                        'no_rawat' => $jurnal->no_rawat,
                        'no_tagihan' => $jurnal->no_tagihan,
                        'kd_pj' => $jurnal->kd_pj,
                        'piutang_dibayar' => $jurnal->besar_cicilan,
                        'tgl_penagihan' => $jurnal->tgl_penagihan,
                        'tgl_jatuh_tempo' => $jurnal->tgl_jatuh_tempo,
                        'tgl_bayar' => $jurnal->tgl_bayar,
                        'status' => $status ? 'Bayar' : 'Batal Bayar',
                        'kd_rek' => $jurnal->kd_rek,
                        'nm_rek' => $jurnal->nama_bank,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                static::insert($data->all());
            });
    }
}
