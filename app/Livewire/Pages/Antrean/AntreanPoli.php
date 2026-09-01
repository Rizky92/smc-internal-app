<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Perawatan\RegistrasiPasien;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AntreanPoli extends Component
{
    /** @var string */
    public $kd_poli;

    public function mount(string $kd_poli): void
    {
        $this->kd_poli = $kd_poli;
    }

    public function getAntreanQuery(): Builder
    {
        return RegistrasiPasien::query()
            ->antrianPoli($this->kd_poli);
    }

    public function getAntreanProperty(): Collection
    {
        return $this->getAntreanQuery()->get();
    }

    #[On('updateAntrean')]
    public function updateAntrean(): void
    {
        $this->dispatch('updateMarqueeData');
    }

    public function getNextAntreanProperty(): ?Model
    {
        return $this->getAntreanQuery()
            ->selectRaw('antripoli.status')
            ->join('antripoli', 'reg_periksa.no_rawat', '=', 'antripoli.no_rawat')
            ->where('antripoli.kd_poli', $this->kd_poli)
            ->where('antripoli.status', '1')
            ->first();
    }

    #[On('call')]
    public function call(): void
    {
        $antrean = $this->getNextAntreanProperty();

        if ($antrean && $antrean->status == '1') {
            $this->dispatch('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_poli'   => $antrean->nm_poli,
            ]);
        }
    }

    #[On('updateStatusAfterCall')]
    public function updateStatusAfterCall(): void
    {
        AntriPoli::where('kd_poli', $this->kd_poli)->update(['status' => '0']);
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-poli');
    }
}
