<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\Kamar;
use App\Models\Perawatan\RawatInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class DaftarPasienRanap extends Component
{
    use WithPagination, FlashComponent, Filterable;

    public $cari;

    public $perpage;

    public $tglAwal;

    public $tglAkhir;

    public $statusPerawatan;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'updateHargaKamar',
        'beginExcelExport',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'perpage' => [
                'except' => 25,
            ],
            'statusPerawatan' => [
                'except' => '-',
                'as' => 'status_perawatan'
            ],
            'tglAwal' => [
                'except' => now()->format('Y-m-d'),
                'as' => 'tgl_awal',
            ],
            'tglAkhir' => [
                'except' => now()->format('Y-m-d'),
                'as' => 'tgl_akhir',
            ],
        ];
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->statusPerawatan = '-';
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty()
    {
        return RegistrasiPasien::daftarPasienRanap(
            $this->cari,
            $this->statusPerawatan,
            $this->tglAwal,
            $this->tglAkhir,
        )
            ->orderBy('no_rawat')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.perawatan.daftar-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Daftar Pasien Rawat Inap']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_daftar_pasien_ranap";

        $titles = [
            'RS Samarinda Medika Citra',
            'Daftar Pasien Rawat Inap',
            Carbon::parse($this->tglAwal)->format('d F Y') . ' - ' . Carbon::parse($this->tglAkhir)->format('d F Y'),
        ];

        $columnHeaders = [
            'No. Rawat',
            'No. RM',
            'Kamar',
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
            'Tarif',
            'Dokter P.J.',
            'No. HP',
        ];

        $data = RegistrasiPasien::daftarPasienRanap(
            '',
            $this->statusPerawatan,
            $this->tglAwal,
            $this->tglAkhir,
            $this->jamAwal,
            $this->jamAkhir,
            true
        )
            ->orderBy('no_rawat')
            ->get();

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData($data);

        return $excel->export();
    }

    public function batalkanRanapPasien(string $noRawat, string $tglMasuk, string $jamMasuk, string $kamar)
    {
        if (!auth()->user()->can('perawatan.rawat-inap.batal-ranap')) {
            $this->flashError('Anda tidak dapat melakukan aksi ini');

            return;
        }

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

        $this->flashSuccess("Data pasien dengan No. Rawat {$noRawat} sudah kembali ke rawat jalan!");
    }

    public function updateHargaKamar(string $noRawat, string $kdKamar, string $tglMasuk, string $jamMasuk, int $hargaKamarBaru, int $lamaInap)
    {
        if (!auth()->user()->can('perawatan.daftar-pasien-ranap.update-harga-kamar')) {
            $this->emitTo('user.manajemen-user', 'flashError', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

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

        $perawatan = RawatInap::where([
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

        $this->flashSuccess('Harga kamar berhasil diupdate!');
    }
}
