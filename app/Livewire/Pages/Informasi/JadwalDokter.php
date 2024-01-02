<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Models\Antrian\Jadwal;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class JadwalDokter extends Component
{
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

    public function getDataJadwalDokterProperty()
    {
        return Jadwal::query()
            ->jadwalDokter($this->semuaPoli)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->get();
    }

    public function mount(): void
    {
        $this->defaultValues();
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
