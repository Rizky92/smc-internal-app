<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Models\Antrian\Jadwal;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class JadwalDokter extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;

    /** @var bool */
    public $semuaPoli;

    protected function queryString(): array
    {
        return [
            'semuaPoli' => ['except' => false, 'as' => 'tampilkan_semua_poli'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataJadwalDokterProperty()
    {
        return $this->isDeferred
            ? []
            : Jadwal::query()
                ->jadwalDokter()
                ->with(['dokter', 'poliklinik'])
                ->when(
                    ! $this->semuaPoli,
                    fn (Builder $query) => $query->where('poliklinik.nm_poli', '<>', 'Poli Eksekutif'),
                )
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns, [
                    'jam_mulai' => 'asc',
                ])
                ->get()
                ->map(function ($item) {
                    // Hitung total registrasi menggunakan fungsi pada model Jadwal
                    [$total_registrasi_jadwal1, $total_registrasi_jadwal2] = Jadwal::hitungTotalRegistrasi(
                        $item->kd_dokter,
                        $item->kd_poli,
                        $item->hari_kerja,
                        now()->toDateString() // Ubah sesuai kebutuhan format tanggal
                    );

                    // Periksa apakah $item memiliki duplikat
                    if ($item->isDuplicate()) {
                        // Ambil jadwal pertama dari hasil sortasi
                        $firstJadwal = Jadwal::where('kd_dokter', $item->kd_dokter)
                            ->where('kd_poli', $item->kd_poli)
                            ->where('hari_kerja', $item->hari_kerja)
                            ->orderBy('jam_mulai', 'asc')
                            ->first();

                        // Periksa apakah $item merupakan jadwal pertama atau kedua
                        if ($item->jam_mulai === $firstJadwal->jam_mulai) {
                            // Jadwal pertama, tampilkan sesuai kuota
                            $item->total_registrasi = min($total_registrasi_jadwal1, $item->kuota);
                        } else {
                            // Jadwal kedua, terus menjumlahkan sisanya
                            $item->total_registrasi = $total_registrasi_jadwal2;
                        }
                    } else {
                        // Jika tidak ada duplikat, hitung total registrasi tanpa batasan kuota
                        $item->total_registrasi = $total_registrasi_jadwal1;
                    }

                    return $item;
                });
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.jadwal-dokter')
            ->layout(BaseLayout::class, ['title' => 'Jadwal Dokter Hari Ini']);
    }

    protected function defaultValues(): void
    {
        $this->semuaPoli = false;
    }
}
