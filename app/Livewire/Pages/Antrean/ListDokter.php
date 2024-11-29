<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\Jadwal;
use Illuminate\View\View;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        Carbon::setLocale('id');
        $pintu_poli = DB::connection('mysql_smc')->table('pintu_poli');
        $manajemen_pintu = DB::connection('mysql_smc')->table('manajemen_pintu');

        return Jadwal::query()
            ->with('dokter')
            ->joinSub($pintu_poli, 'pintu_poli', function (JoinClause $join) {
                $join->on('jadwal.kd_poli', '=', 'pintu_poli.kd_poli');
            })
            ->joinSub($manajemen_pintu, 'manajemen_pintu', function (JoinClause $join) {
                $join->on('pintu_poli.kd_pintu', '=', 'manajemen_pintu.kd_pintu');
            })
            ->where('manajemen_pintu.kd_pintu', $this->kd_pintu)
            ->where('jadwal.hari_kerja', strtoupper(Carbon::now()->translatedFormat('l')))
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.list-dokter');
    }
}