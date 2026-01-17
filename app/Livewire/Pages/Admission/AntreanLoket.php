<?php

namespace App\Livewire\Pages\Admission;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class AntreanLoket extends Component
{
    /** @var string */
    public $loket;

    /** @var string */
    public $antrian;

    protected $listeners = ['queueCalled', 'queueStopped'];

    public function getIklanProperty(): ?object
    {
        return DB::connection('mysql_sik')->table('runtext')->first();
    }

    public function queueCalled($loket, $antrian): void
    {
        $this->loket = $loket;
        $this->antrian = $antrian;
    }

    public function queueStopped(): void
    {
        $this->loket = null;
        $this->antrian = null;
    }

    public function render(): View
    {
        return view('livewire.pages.admission.antrean-loket');
    }
}
