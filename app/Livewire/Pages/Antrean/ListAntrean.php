<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Aplikasi\Pintu;
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

    public function getAntreanPerPintuProperty()
    {
        return Pintu::query()->antrianPerPintu($this->kd_pintu)->get();
    }

    public function updateAntrean(): void
    {
        $this->dispatchBrowserEvent('updateMarqueeData', [
            'rowCount' => $this->antreanPerPintu->count(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.list-antrean');
    }
}
