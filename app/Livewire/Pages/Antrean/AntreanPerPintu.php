<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Aplikasi\Pintu;
use Illuminate\View\View;
use Livewire\Component;

class AntreanPerPintu extends Component
{
    /** @var string */
    public $kd_pintu;

    public function mount(string $kd_pintu): void
    {
        $this->kd_pintu = $kd_pintu;
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-per-pintu');
    }

    public function getAntreanProperty()
    {
        return Pintu::query()
            ->antrianPerPintu($this->kd_pintu)
            ->where('registrasi.stts', 'Belum')
            ->get();
    }
}
