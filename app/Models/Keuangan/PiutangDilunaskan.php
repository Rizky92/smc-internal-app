<?php

namespace App\Models\Keuangan;

use App\Support\Traits\Eloquent\Searchable;
use App\Support\Traits\Eloquent\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class PiutangDilunaskan extends Model
{
    use Sortable, Searchable;

    protected $connection = 'mysql_smc';

    protected $table = 'piutang_dilunaskan';

    public function refreshModel()
    {
        $latest = static::latest('waktu_jurnal')->value('waktu_jurnal');

        $sqlSelect = <<<SQL
            jurnal.no_jurnal,
            timestamp(jurnal.tgl_jurnal, jurnal.jam_jurnal) waktu_jurnal,
            detail_penagihan_piutang.no_rawat,
            penagihan_piutang.no_tagihan,
            penagihan_piutang.kd_pj,
            
            jurnal.no_jurnal,
            jurnal.no_bukti,
            penagihan_piutang.tanggal tgl_penagihan,
            penagihan_piutang.tanggaltempo tgl_jatuh_tempo,
            penagihan_piutang.nip nip_penagih,
            pegawai_penagih.nama as penagih,
            penagihan_piutang.nip_menyetujui,
            pegawai_menyetujui.nama as menyetujui,
            penjab.nama_perusahaan,
            penjab.png_jawab,
            penagihan_piutang.catatan,
            round(sum(detailjurnal.debet), 2) debet,
            round(sum(detailjurnal.kredit), 2) kredit,
            penagihan_piutang.kd_rek,
            akun_penagihan_piutang.nama_bank,
            akun_penagihan_piutang.no_rek,
            penagihan_piutang.status,
            jurnal.keterangan
        SQL;

        $sqlGroupBy = <<<SQL
            penagihan_piutang.no_tagihan,
            timestamp(jurnal.tgl_jurnal, jurnal.jam_jurnal),
            penagihan_piutang.tanggal,
            penagihan_piutang.tanggaltempo,
            penagihan_piutang.nip,
            bagianpenagihan.nama,
            penagihan_piutang.nip_menyetujui,
            menyetujui.nama,
            penagihan_piutang.kd_pj,
            penjab.nama_perusahaan,
            penjab.png_jawab,
            penagihan_piutang.catatan,
            jurnal.no_jurnal,
            jurnal.keterangan,
            penagihan_piutang.kd_rek,
            akun_penagihan_piutang.nama_bank,
            akun_penagihan_piutang.no_rek,
            penagihan_piutang.status
        SQL;

        DB::connection('mysql_sik')
            ->table('jurnal')
            ->selectRaw($sqlSelect)
            ->leftJoin('detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->leftJoin('detail_penagihan_piutang', 'jurnal.no_bukti', '=', 'detail_penagihan_piutang.no_rawat')
            ->leftJoin('penagihan_piutang', 'detail_penagihan_piutang.no_tagihan', '=', 'penagihan_piutang.no_tagihan')
            ->join(DB::raw('pegawai pegawai_penagih'), 'penagihan_piutang.nip', '=', 'pegawai_penagih.nik')
            ->join(DB::raw('pegawai pegawai_menyetujui'), 'penagihan_piutang.nip_menyetujui', '=', 'pegawai_menyetujui.nik')
            ->join('penjab', 'penagihan_piutang.kd_pj', '=', 'penjab.kd_pj')
            ->join('akun_penagihan_piutang', 'penagihan_piutang.kd_rek', '=', 'akun_penagihan_piutang.kd_rek')
            ->when(
                !is_null($latest),
                fn ($q) => $q->whereRaw("timestamp(tgl_jurnal, jam_jurnal) > ?", $latest->waktu_jurnal),
                fn ($q) => $q->where('tgl_jurnal', '>=', '2022-10-31')
            )
            ->where('penagihan_piutang.status', 'Sudah Dibayar')
            ->where('akun_penagihan_piutang.kd_rek', '112010')
            ->where(
                fn ($q) => $q
                    ->where('keterangan', 'like', '%bayar piutang, oleh%')
                    ->orWhere('keterangan', 'like', '%pembatalan bayar piutang, oleh%')
            )
            ->groupByRaw($sqlGroupBy)
            ->orderBy('jurnal.tgl_jurnal')
            ->orderBy('jurnal.jam_jurnal')
            ->chunk(500, function ($collection) {
                /** @var \Illuminate\Support\Collection $collection */

                $data = $collection->map(function ($jurnal, $key) {
                    $ket = Str::of($jurnal->keterangan);

                    $status = $ket->startsWith('PEMBATALAN');

                    return new Fluent([
                        'no_jurnal' => $jurnal->no_jurnal,
                        'no_rawat' => $jurnal->no_bukti,
                        'waktu_jurnal' => "{$jurnal->tgl_jurnal} {$jurnal->jam_jurnal}",
                        'status' => $status ? 'Bayar' : 'Belum Bayar',
                    ]);
                });
            });
    }

    public static function truncateTable()
    {
        static::truncate();
    }
}
