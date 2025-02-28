<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Aplikasi\Pintu;
use Illuminate\View\View;
use Livewire\Component;

class ListDokter extends Component
{
    /** @var string */
    public $kd_pintu;

    public function mount(string $kd_pintu): void
    {
        $this->kd_pintu = $kd_pintu;
    }

    public function getListDokterProperty()
    {
        return Pintu::query()->dokterPerPintu($this->kd_pintu)->get();
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.list-dokter');
    }
}
