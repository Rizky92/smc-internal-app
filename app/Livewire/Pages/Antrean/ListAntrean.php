<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Aplikasi\Pintu;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class ListAntrean extends Component
{
    /** @var string */
    public $kd_pintu;

    /** @var mixed */
    protected $listeners = ['updateAntrean'];

    public function mount(string $kd_pintu): void
    {
        $this->kd_pintu = $kd_pintu;
    }

    public function getAntreanPerPintuProperty(): Collection
    {
        $data = Pintu::query()
            ->antreanPerPintu($this->kd_pintu, 'list')
            ->where('reg_periksa.tgl_registrasi', now()->toDateString())
            ->orderBy('jadwal.jam_mulai', 'asc')
            ->orderBy('dokter.nm_dokter', 'asc')
            ->orderBy('reg_periksa.no_reg', 'asc')
            ->get();

        return $data->groupBy(fn ($item) => $item->kd_dokter.'_'.$item->jam_mulai)
            ->map(fn ($items) => [
                'header' => [
                    'nm_dokter'   => $items->first()->nm_dokter,
                    'jam_mulai'   => Carbon::parse($items->first()->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($items->first()->jam_selesai)->format('H:i'),
                ],
                'items' => $items,
            ])
            ->values();
    }

    public function getTotalRowProperty(): int
    {
        return $this->antreanPerPintu->sum(fn ($group) => 1 + $group['items']->count());
    }

    public function updateAntrean(): void
    {
        $this->dispatchBrowserEvent('updateMarqueeData', [
            'rowCount' => $this->totalRow,
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.list-antrean');
    }
}
