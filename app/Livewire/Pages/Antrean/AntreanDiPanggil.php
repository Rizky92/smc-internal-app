<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Aplikasi\Pintu;
use Illuminate\Database\Query\JoinClause;
use Illuminate\View\View;
use Livewire\Component;

class AntreanDiPanggil extends Component
{
    /** @var string */
    public $kd_pintu;

    /** @var bool */
    public $isCalling = false;

    /** @var mixed */
    protected $lastCalledPatient = null;

    /** @var mixed */
    protected $listeners = ['updateStatusAfterCall'];

    public function mount(string $kd_pintu): void
    {
        $this->kd_pintu = $kd_pintu;
        $cachedPatient = cache("lastCalledPatient_{$kd_pintu}", null);
        $this->lastCalledPatient = is_string($cachedPatient) ? unserialize($cachedPatient) : null;
    }

    public function getAntreanDiPanggilProperty()
    {
        $db = \DB::connection('mysql_sik')->getDatabaseName();

        $antripoli = \DB::raw("{$db}.antripoli antripoli");

        return Pintu::query()
            ->antrianPerPintu($this->kd_pintu)
            ->selectRaw('antripoli.status')
            ->leftJoin($antripoli, fn (JoinClause $join) => $join
                ->on('registrasi.no_rawat', '=', 'antripoli.no_rawat')
                ->on('poliklinik.kd_poli', '=', 'antripoli.kd_poli')
                ->where('antripoli.status', '1')
            )
            ->where('antripoli.status', '1')
            ->first();
    }

    public function call(): void
    {
        if ($this->isCalling) {
            return;
        }

        $antrean = $this->antreanDiPanggil;

        if ($antrean && $antrean->status === '1') {
            $this->lastCalledPatient = $antrean;

            cache()->put("lastCalledPatient_{$this->kd_pintu}", serialize($antrean), now()->addHours(12));

            $this->isCalling = true;
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_poli'   => $antrean->nm_poli,
            ]);
        } else {
            $cachedPatient = cache("lastCalledPatient_{$this->kd_pintu}", null);
            if (is_string($cachedPatient)) {
                $this->lastCalledPatient = unserialize($cachedPatient);
            } else {
                $this->lastCalledPatient = null;
            }
        }
    }

    public function updateStatusAfterCall(): void
    {
        $antrean = $this->antreanDiPanggil;

        if ($antrean) {
            AntriPoli::query()
                ->where('no_rawat', $antrean->no_rawat)
                ->where('kd_poli', $antrean->kd_poli)
                ->where('status', '1')
                ->update(['status' => '0']);
        }

        $this->isCalling = false;
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-di-panggil', [
            'currentPatient' => $this->antreanDiPanggil ?? $this->lastCalledPatient,
        ]);
    }
}
