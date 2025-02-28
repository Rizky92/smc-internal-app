<?php

namespace App\Livewire\Pages\Perawatan;

use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\KamarInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;

class DaftarPasienRanap extends Component
{
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $jenisRawat;

    protected function queryString(): array
    {
        return [
            'tglAwal'    => ['except' => now()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'   => ['except' => now()->toDateString(), 'as' => 'tgl_akhir'],
            'jenisRawat' => ['except' => '-', 'as' => 'status'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->daftarPasienRanap($this->tglAwal, $this->tglAkhir, $this->jenisRawat)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.perawatan.daftar-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Daftar Pasien Rawat Inap']);
    }

    public function updateHargaKamar(string $noRawat, string $kodeKamar, string $tglMasuk, string $jamMasuk, int $hargaKamarBaru, int $lamaInap): void
    {
        if (user()->cannot('perawatan.daftar-pasien-ranap.update-harga-kamar')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $validator = Validator::make([
            'harga_kamar_baru' => $hargaKamarBaru,
            'lama_inap'        => $lamaInap,
        ], [
            'harga_kamar_baru' => ['integer', 'numeric', 'min:0'],
            'lama_inap'        => ['integer', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            $this->flashError('Ada data salah, silahkan dicek input anda.');

            return;
        }

        tracker_start('mysql_sik');

        KamarInap::query()
            ->where([
                ['no_rawat', '=', $noRawat],
                ['kd_kamar', '=', $kodeKamar],
                ['tgl_masuk', '=', carbon($tglMasuk)->toDateString()],
                ['jam_masuk', '=', carbon($jamMasuk)->format('H:i:s')],
            ])
            ->update([
                'trf_kamar' => $hargaKamarBaru,
                'lama'      => $lamaInap,
                'ttl_biaya' => $hargaKamarBaru * $lamaInap,
            ]);

        tracker_end('mysql_sik');

        $this->resetFilters();
        $this->dispatchBrowserEvent('data-updated');

        $this->flashSuccess('Harga kamar berhasil diupdate!');
    }

    protected function defaultValues(): void
    {
        $this->jenisRawat = '-';
        $this->tglAwal = now()->toDateString();
        $this->tglAkhir = now()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
                ->daftarPasienRanap($this->tglAwal, $this->tglAkhir, $this->jenisRawat)
                ->orderBy('no_rawat')
                ->cursor()
                ->map(fn (RegistrasiPasien $model): array => [
                    'no_rawat'     => $model->no_rawat,
                    'no_rkm_medis' => $model->no_rkm_medis,
                    'kamar'        => "{$model->kd_kamar} {$model->nm_bangsal}",
                    'kelas'        => $model->kelas,
                    'pasien'       => $model->nm_pasien,
                    'alamat'       => $model->alamat,
                    'agama'        => $model->agama,
                    'p_jawab'      => $model->pj,
                    'jenis_bayar'  => $model->png_jawab,
                    'asal_poli'    => $model->nm_poli,
                    'dokter_poli'  => $model->dokter_poli,
                    'status'       => $model->stts_pulang,
                    'tgl_masuk'    => $model->tgl_masuk,
                    'jam_masuk'    => $model->jam_masuk,
                    'tgl_keluar'   => $model->tgl_keluar,
                    'jam_keluar'   => $model->jam_keluar,
                    'tarif_kamar'  => (float) $model->trf_kamar,
                    'lama'         => (int) $model->lama,
                    'total'        => (float) $model->ttl_biaya,
                    'dpjp'         => $model->dokter_ranap,
                    'no_hp'        => $model->no_tlp,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Kamar',
            'Kelas',
            'Pasien',
            'Alamat',
            'Agama',
            'P.J.',
            'Jenis Bayar',
            'Asal Poli',
            'Dokter Poli',
            'Status',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Keluar',
            'Jam Keluar',
            'Tarif Kamar (RP)',
            'Lama',
            'Total (RP)',
            'DPJP',
            'No. HP',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Daftar Pasien Rawat Inap',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
