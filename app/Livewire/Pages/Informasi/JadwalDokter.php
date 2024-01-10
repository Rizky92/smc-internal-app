<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\View\Components\BaseLayout;
use App\Models\Antrian\Jadwal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class JadwalDokter extends Component
{
    use FlashComponent, Filterable, LiveTable, DeferredLoading;

    /** @var bool */
    public $semuaPoli;

    protected function queryString(): array
    {
        return [
            'semuaPoli'     => ['except' => false, 'as' => 'tampilkan_semua_poli'],
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
            ->paginate($this->perpage);
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

