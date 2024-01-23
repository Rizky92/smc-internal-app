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
                    !$this->semuaPoli,
                    fn (Builder $query) => $query->where('poliklinik.nm_poli', '<>', 'Poli Eksekutif'),
                )
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns, [
                    'jam_mulai' => 'asc',
                ])
                ->paginate($this->perpage)
                ->map(function ($item) {
                    // Hitung total registrasi menggunakan fungsi pada model Jadwal
                    [$total_registrasi_jadwal1, $total_registrasi_jadwal2] = Jadwal::hitungTotalRegistrasi(
                        $item->kd_dokter,
                        $item->kd_poli,
                        $item->hari_kerja,
                        now()->format('Y-m-d') // Ubah sesuai kebutuhan format tanggal
                    );

                    // Periksa apakah saat ini adalah jadwal pertama atau kedua
                    $current_jadwal = ($item->jam_mulai <= now()->format('H:i:s')) ? $total_registrasi_jadwal1 : $total_registrasi_jadwal2;

                    // Update property pada objek item dengan total registrasi yang sesuai
                    $item->total_registrasi = $current_jadwal;

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
