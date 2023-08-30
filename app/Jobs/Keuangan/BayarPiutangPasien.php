<?php

namespace App\Jobs\Keuangan;

use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\PenagihanPiutang;
use App\Models\Keuangan\PiutangPasien;
use App\Models\Keuangan\PiutangPasienDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BayarPiutangPasien implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $tglAwal;
    private string $tglAkhir;
    private string $jaminanPasien;
    private string $jenisPerawatan;

    private string $dataDipilih;
    private string $tglBayar;
    private string $userId;
    private string $akun;
    private string $akunDiskonPiutang;
    private string $akunTidakTerbayar;

    private float $totalPiutang;
    private float $cicilanSekarang;

    /**
     * Create a new job instance.
     * 
     * @param  array{
     *     tgl_awal: string,
     *     tgl_akhir: string,
     *     jaminan_pasien: string,
     *     jenis_perawatan: string,
     *     data: string,
     *     tgl_bayar: string,
     *     user_id: string,
     *     akun: string,
     *     akun_diskon_piutang: string,
     *     akun_tidak_terbayar: string,
     * } $params
     */
    public function __construct(array $params)
    {
        $this->tglAwal = $params['tgl_awal'];
        $this->tglAkhir = $params['tgl_akhir'];
        $this->jaminanPasien = $params['jaminan_pasien'];
        $this->jenisPerawatan = $params['jenis_perawatan'];
        $this->dataDipilih = $params['data'];
        $this->tglBayar = $params['tgl_bayar'];
        $this->userId = $params['user_id'];
        $this->akun = $params['akun'];
        $this->akunDiskonPiutang = $params['akun_diskon_piutang'];
        $this->akunTidakTerbayar = $params['akun_tidak_terbayar'];
    }

    public function handle(): void
    {
        $this->proceed();
    }

    protected function proceed(): void
    {
        $model = PenagihanPiutang::query()
            ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
            ->whereRaw(
                'concat(penagihan_piutang.no_tagihan, "_", penagihan_piutang.kd_pj, "_", detail_penagihan_piutang.no_rawat) = ?',
                $this->dataDipilih
            )
            ->first();

        DB::connection('mysql_sik')
            ->transaction(function () use ($model) {
                tracker_start('mysql_sik');

                BayarPiutang::insert([
                    'tgl_bayar'             => $this->tglBayar,
                    'no_rkm_medis'          => $model->no_rkm_medis,
                    'catatan'               => sprintf('diverifikasi oleh %s', $this->userId),
                    'no_rawat'              => $model->no_rawat,
                    'kd_rek'                => $this->akun,
                    'kd_rek_kontra'         => $model->kd_rek,
                    'besar_cicilan'         => $model->sisa_piutang,
                    'diskon_piutang'        => 0,
                    'kd_rek_diskon_piutang' => $this->akunDiskonPiutang,
                    'tidak_terbayar'        => 0,
                    'kd_rek_tidak_terbayar' => $this->akunTidakTerbayar,
                ]);

                $this->setLunasPiutang($model->no_rawat, $model->no_rkm_medis, $model->nama_bayar, $model->kd_pj_tagihan);

                $this->setSelesaiPenagihanPiutang($model->no_tagihan, $model->kd_rek);

                Jurnal::catat($model->no_rawat, 'U', sprintf('BAYAR PIUTANG TAGIHAN %s, OLEH %s', $model->no_tagihan, $this->userId), $this->tglBayar, [
                    ['kd_rek' => $this->akun, 'debet' => $model->sisa_piutang, 'kredit' => 0],
                    ['kd_rek' => $model->kd_rek, 'debet' => 0, 'kredit' => $model->sisa_piutang],
                ]);

                tracker_end('mysql_sik', $this->userId);
            });
    }

    /**
     * @template T
     */
    protected function setLunasPiutang(
        string $noRawat,
        string $noRM,
        string $namaBayar,
        string $kodePenjamin
    ): void {
        if (empty($noRawat) || empty($noRM) || empty($namaBayar) || empty($kodePenjamin)) {
            return;
        }

        /** @var \Closure(T): T */
        $query = fn ($q) => $q->where([
            ['nama_bayar', '=', $namaBayar],
            ['kd_pj', '=', $kodePenjamin],
        ]);

        $piutangPasien = PiutangPasien::query()
            ->with(['detail' => $query])
            ->where('no_rawat', $noRawat)
            ->whereHas('detail', $query)
            ->first();

        $this->totalPiutang = PiutangPasienDetail::query()
            ->where('no_rawat', $noRawat)
            ->sum('sisapiutang');

        $this->totalPiutang = intval(round(floatval($this->totalPiutang)));

        $this->cicilanSekarang = BayarPiutang::query()
            ->where('no_rawat', $noRawat)
            ->where('no_rkm_medis', $noRM)
            ->sum(DB::raw('besar_cicilan + diskon_piutang + tidak_terbayar'));

        $this->cicilanSekarang = intval(round(floatval($this->cicilanSekarang)));

        if (is_null($piutangPasien) || ($this->totalPiutang - $this->cicilanSekarang) > 0) {
            return;
        }

        $piutangPasien->update(['status' => 'Lunas']);
    }

    protected function setSelesaiPenagihanPiutang(string $noTagihan, string $akunKontra): void
    {
        $tagihanPiutang = PenagihanPiutang::query()
            ->with('detail')
            ->where('no_tagihan', $noTagihan)
            ->first();
            
        if (is_null($tagihanPiutang)) {
            return;
        }

        $totalTagihanPiutang = $tagihanPiutang->detail->sum('sisapiutang');
        $totalTagihanPiutang = intval(round(floatval($totalTagihanPiutang)));

        $piutangDibayar = BayarPiutang::query()
            ->whereIn('no_rawat', $tagihanPiutang->detail->pluck('no_rawat')->all())
            ->where('kd_rek', $this->akun)
            ->where('kd_rek_kontra', $akunKontra)
            ->sum(DB::raw('besar_cicilan + diskon_piutang + tidak_terbayar'));

        $piutangDibayar = intval(round(floatval($piutangDibayar)));

        if ($totalTagihanPiutang !== $piutangDibayar) {
            return;
        }

        $tagihanPiutang->update(['status' => 'Sudah Dibayar']);
    }
}
