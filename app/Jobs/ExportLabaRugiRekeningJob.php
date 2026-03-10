<?php

namespace App\Jobs;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\RekamMedis\Penjamin;
use Illuminate\Support\Fluent;

class ExportLabaRugiRekeningJob extends ExcelExportJob
{
    protected function filename(): string
    {
        return 'laba-rugi-rekening';
    }

    protected function columnHeaders(): array
    {
        return ['Unit', 'Dokter', 'Kode Akun', 'Nama Akun', 'Jenis', 'Debet', 'Kredit', 'Total'];
    }

    protected function pageHeaders(): array
    {
        ['tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir, 'kodePenjamin' => $kodePenjamin] = $this->payload;

        $penjamin = empty($kodePenjamin)
            ? 'SEMUA'
            : Penjamin::where('kd_pj', $kodePenjamin)->value('png_jawab');

        $periodeAwal = carbon($tglAwal);
        $periodeAkhir = carbon($tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Laba Rugi Keuangan penjamin ' . $penjamin,
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }

    protected function dataPerSheet(): array
    {
        ['tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir, 'kodePenjamin' => $kodePenjamin] = $this->payload;

        $detailPerRekening = Jurnal::query()
            ->labaRugiRalan($tglAwal, $tglAkhir, $kodePenjamin)
            ->unionAll(Jurnal::query()->labaRugiRanap($tglAwal, $tglAkhir, $kodePenjamin))
            ->unionAll(Jurnal::query()->labaRugi($tglAwal, $tglAkhir, $kodePenjamin))
            ->get()
            ->map(fn ($item) => new Fluent([
                'unit'      => $item->unit,
                'nm_dokter' => $item->nm_dokter ?: '-',
                'kd_rek'    => $item->kd_rek,
                'nm_rek'    => $item->nm_rek,
                'balance'   => $item->balance,
                'debet'     => floatval($item->debet),
                'kredit'    => floatval($item->kredit),
                'total'     => $item->balance === 'K'
                    ? floatval($item->kredit - $item->debet)
                    : floatval($item->debet - $item->kredit),
            ]))
            ->mapToGroups(fn ($item) => [$item->balance => $item]);

        $detailPerRekening = collect(['D' => collect(), 'K' => collect()])->merge($detailPerRekening);

        $pendapatan = $detailPerRekening->get('K', collect());
        $beban = $detailPerRekening->get('D', collect());

        $totalDebetPendapatan   = $pendapatan->sum('debet');
        $totalKreditPendapatan  = $pendapatan->sum('kredit');
        $totalPendapatan        = $totalKreditPendapatan - $totalDebetPendapatan;
        $totalDebetBeban        = $beban->sum('debet');
        $totalKreditBeban       = $beban->sum('kredit');
        $totalBebanDanBiaya     = $totalDebetBeban - $totalKreditBeban;
        $labaRugi               = $totalPendapatan - $totalBebanDanBiaya;

        $insertRow = fn (...$args) => new Fluent([
            'unit' => $args[0] ?? '', 'nm_dokter' => $args[1] ?? '',
            'kd_rek' => $args[2] ?? '', 'nm_rek' => $args[3] ?? '',
            'balance' => $args[4] ?? '', 'debet' => $args[5] ?? '',
            'kredit' => $args[6] ?? '', 'total' => $args[7] ?? '',
        ]);

        $empty = $insertRow();

        return [collect([$insertRow('', 'PENDAPATAN')])
            ->merge($pendapatan)
            ->merge([$insertRow('', 'TOTAL PENDAPATAN', '', '', '', $totalDebetPendapatan, $totalKreditPendapatan, $totalPendapatan), $empty])
            ->merge([$insertRow('', 'BEBAN & BIAYA')])
            ->merge($beban)
            ->merge([$insertRow('', 'TOTAL BEBAN & BIAYA', '', '', '', $totalDebetBeban, $totalKreditBeban, $totalBebanDanBiaya), $empty])
            ->merge([$insertRow('', 'PENDAPATAN BERSIH', '', '', '', $totalPendapatan, $totalBebanDanBiaya, $labaRugi)])
        ];
    }
}