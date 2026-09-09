<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Aplikasi\Pintu;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ListAntrean extends Component
{
    /** @var string */
    public $kd_pintu;

    public function mount(string $kd_pintu): void
    {
        $this->kd_pintu = $kd_pintu;
    }

    /**
     * Null when kd_pintu names no pintu.
     *
     * Queried here rather than inline in the view, where the result was
     * dereferenced without allowing for a code that no longer exists. value()
     * returns null instead of a model, so there is nothing to dereference.
     */
    public function getNamaPintuProperty(): ?string
    {
        return Pintu::query()
            ->where('kd_pintu', $this->kd_pintu)
            ->value('nm_pintu');
    }

    public function getAntreanPerPintuProperty()
    {
        return Pintu::query()
            ->antreanPerPintu($this->kd_pintu, 'list')
            ->where('reg_periksa.tgl_registrasi', now()->toDateString())
            ->orderBy('jadwal.jam_mulai', 'asc')
            ->orderBy('dokter.nm_dokter', 'asc')
            ->orderBy('reg_periksa.no_reg', 'asc')
            ->get();
    }

    #[On('updateAntrean')]
    public function updateAntrean(): void
    {
        $this->dispatch('updateMarqueeData', [
            'rowCount' => $this->antreanPerPintu->count(),
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.list-antrean');
    }
}
