<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\RawatInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarPasienRanap extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    public $statusPerawatan;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->format('Y-m-d'), 'as' => 'periode_awal'],
            'periodeAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'periode_akhir'],
            'statusPerawatan' => ['except' => '-', 'as' => 'status'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty()
    {
        return RegistrasiPasien::query()
            ->daftarPasienRanap(
                $this->periodeAwal,
                $this->periodeAkhir,
                $this->statusPerawatan
            )
            ->search($this->cari, [
                "kamar_inap.kd_kamar",
                "reg_periksa.no_rawat",
                "reg_periksa.no_rkm_medis",
                "concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal)",
                "kamar.kelas",
                "concat(pasien.nm_pasien, ' (', reg_periksa.umurdaftar, ' ', reg_periksa.sttsumur, ')')",
                "concat(pasien.alamat, ', Kel. ', kelurahan.nm_kel, ', Kec. ', kecamatan.nm_kec, ', ', kabupaten.nm_kab, ', ', propinsi.nm_prop)",
                "pasien.agama",
                "concat(pasien.namakeluarga, ' (', pasien.keluarga, ')')",
                "penjab.png_jawab",
                "poliklinik.nm_poli",
                "dokter.nm_dokter",
                "kamar_inap.stts_pulang",
                "ifnull(dokter_pj.nm_dokter, '-')",
                "pasien.no_tlp",
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

    public function render()
    {
        return view('livewire.perawatan.daftar-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Daftar Pasien Rawat Inap']);
    }

    /*
    public function batalkanRanapPasien(string $noRawat, string $tglMasuk, string $jamMasuk, string $kamar)
    {
        if (!auth()->user()->can('perawatan.rawat-inap.batal-ranap')) {
            $this->flashError('Anda tidak dapat melakukan aksi ini');

            return;
        }

        tracker_start();

        RawatInap::where([
            ['no_rawat', '=', $noRawat],
            ['tgl_masuk', '=', $tglMasuk],
            ['jam_masuk', '=', $jamMasuk],
            ['kd_kamar', '=', $kamar]
        ])
            ->delete();

        Kamar::find($kamar)->update(['status' => 'KOSONG']);

        if (!RawatInap::where('no_rawat', $noRawat)->exists()) {
            RegistrasiPasien::find($noRawat)->update([
                'status_lanjut' => 'Ralan',
                'stts' => 'Sudah',
            ]);
        }

        tracker_end();

        $this->flashSuccess("Data pasien dengan No. Rawat {$noRawat} sudah kembali ke rawat jalan!");
    }
    */

    public function updateHargaKamar(string $noRawat, string $kdKamar, string $tglMasuk, string $jamMasuk, int $hargaKamarBaru, int $lamaInap)
    {
        if (!auth()->user()->can('perawatan.daftar-pasien-ranap.update-harga-kamar')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $validator = Validator::make([
            'harga_kamar_baru' => $hargaKamarBaru,
            'lama_inap' => $lamaInap,
        ], [
            'harga_kamar_baru' => ['integer', 'numeric', 'min:0'],
            'lama_inap' => ['integer', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            $this->flashError('Ada data salah, silahkan dicek input anda.');

            return;
        }

        tracker_start();

        RawatInap::where([
            ['no_rawat', '=', $noRawat],
            ['kd_kamar', '=', $kdKamar],
            ['tgl_masuk', '=', Carbon::parse($tglMasuk)->format('Y-m-d')],
            ['jam_masuk', '=', Carbon::parse($jamMasuk)->format('H:i:s')],
        ])->update([
            'trf_kamar' => $hargaKamarBaru,
            'lama' => $lamaInap,
            'ttl_biaya' => $hargaKamarBaru * $lamaInap
        ]);

        tracker_end();

        $this->resetFilters();
        $this->dispatchBrowserEvent('data-tersimpan');

        $this->flashSuccess('Harga kamar berhasil diupdate!');
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->statusPerawatan = '-';
        $this->periodeAwal = now()->format('Y-m-d');
        $this->periodeAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            RegistrasiPasien::query()
                ->daftarPasienRanap(
                    $this->periodeAwal,
                    $this->periodeAkhir,
                    $this->statusPerawatan,
                    true
                )
                ->orderBy('no_rawat')
                ->get()
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
            'Lama (RP)',
            'Total (RP)',
            'DPJP',
            'No. HP',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Daftar Pasien Rawat Inap',
            Carbon::parse($this->periodeAwal)->format('d F Y') . ' - ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
