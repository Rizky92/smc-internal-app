<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\RawatInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;

class DaftarPasienRanap extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $statusPerawatan;

    protected function queryString(): array
    {
        return [
            'tglAwal'         => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'        => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'statusPerawatan' => ['except' => '-', 'as' => 'status'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->daftarPasienRanap(
                $this->tglAwal,
                $this->tglAkhir,
                $this->statusPerawatan
            )
            ->search($this->cari, [
                'kamar_inap.kd_kamar',
                'kamar.kd_kamar',
                'bangsal.kd_bangsal',
                'bangsal.nm_bangsal',
                'kamar.kelas',
                'pasien.nm_pasien',
                'pasien.alamat',
                'kelurahan.nm_kel',
                'kecamatan.nm_kec',
                'kabupaten.nm_kab',
                'propinsi.nm_prop',
                'pasien.agama',
                'pasien.namakeluarga',
                'pasien.keluarga',
                'penjab.png_jawab',
                'poliklinik.nm_poli',
                'dokter.nm_dokter',
                'kamar_inap.stts_pulang',
                'ifnull(dokter_pj.nm_dokter, "-")',
                'pasien.no_tlp',
            ])
            ->sortWithColumns($this->sortColumns, [
                'ruangan'       => DB::raw("concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal)"),
                'data_pasien'   => DB::raw("concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')')"),
                'alamat_pasien' => DB::raw("concat(pasien.alamat, ', Kel. ', kelurahan.nm_kel, ', Kec. ', kecamatan.nm_kec, ', ', kabupaten.nm_kab, ', ', propinsi.nm_prop)"),
                'pj'            => DB::raw("concat(pasien.namakeluarga, ' (', pasien.keluarga, ')')"),
                'dokter_poli'   => "dokter.nm_dokter",
                'tgl_keluar'    => DB::raw("if(kamar_inap.tgl_keluar = '0000-00-00', '-', kamar_inap.tgl_keluar)"),
                'jam_keluar'    => DB::raw("if(kamar_inap.jam_keluar = '00:00:00', '-', kamar_inap.jam_keluar)"),
                'dokter_ranap'  => DB::raw("group_concat(dokter_pj.nm_dokter separator ', ')"),
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.perawatan.daftar-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Daftar Pasien Rawat Inap']);
    }

    public function updateHargaKamar(string $noRawat, string $kdKamar, string $tglMasuk, string $jamMasuk, int $hargaKamarBaru, int $lamaInap): void
    {
        if (!Auth::user()->can('perawatan.daftar-pasien-ranap.update-harga-kamar')) {
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

        RawatInap::where([
            ['no_rawat', '=', $noRawat],
            ['kd_kamar', '=', $kdKamar],
            ['tgl_masuk', '=', carbon($tglMasuk)->format('Y-m-d')],
            ['jam_masuk', '=', carbon($jamMasuk)->format('H:i:s')],
        ])
            ->update([
                'trf_kamar' => $hargaKamarBaru,
                'lama'      => $lamaInap,
                'ttl_biaya' => $hargaKamarBaru * $lamaInap
            ]);

        tracker_end('mysql_sik');

        $this->resetFilters();
        $this->dispatchBrowserEvent('data-updated');

        $this->flashSuccess('Harga kamar berhasil diupdate!');
    }

    protected function defaultValues(): void
    {
        $this->statusPerawatan = '-';
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->daftarPasienRanap($this->tglAwal, $this->tglAkhir, $this->statusPerawatan)
                ->orderBy('no_rawat')
                ->get()
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
                ])
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

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

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
