<?php

namespace App\Livewire\Pages\Aplikasi\Concerns;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Rules\DoesntExist;
use App\Settings\RKATSettings;
use Illuminate\Support\Facades\Auth;

trait PengaturanRKAT
{
    /** @var string */
    public $tahunRKAT;

    /** @var string */
    public $tglAwalPenetapanRKAT;

    /** @var string */
    public $tglAkhirPenetapanRKAT;

    public static function getPengaturanRKATPermissions(): array
    {
        return [
            'aplikasi.pengaturan-rkat.read',
            'aplikasi.pengaturan-rkat.update',
        ];
    }

    public function mountPengaturanRKAT(): void
    {
        $this->defaultValuesPengaturanRKAT();
    }

    public function getDataTahunProperty(): array
    {
        $firstRKAT = AnggaranBidang::query()
            ->orderBy('tahun', 'asc')
            ->limit(1)
            ->value('tahun');

        return collect(range($firstRKAT, (int) now()->addYears(5)->format('Y'), 1))
            ->mapWithKeys(fn (int $v, $_): array => [$v => $v])
            ->all();
    }

    public function updatePengaturanRKAT(): void
    {
        if (!Auth::user()->can('aplikasi.pengaturan-rkat.update')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('pengaturan-rkat.data-denied');

            return;
        }

        $validated = $this->validate([
            'tahunRKAT'              => ['required', new DoesntExist(AnggaranBidang::class, 'tahun')],
            'tglAwalPenetapanRKAT'   => ['required', 'date'],
            'tglAkhirPenetapanRKAT'  => ['required', 'date'],
        ]);

        tracker_start();

        app(RKATSettings::class)
            ->fill([
                'tahun'               => $validated['tahunRKAT'],
                'tgl_penetapan_awal'  => carbon($validated['tglAwalPenetapanRKAT']),
                'tgl_penetapan_akhir' => carbon($validated['tglAkhirPenetapanRKAT']),
            ])
            ->save();

        tracker_end();

        $this->emit('flash.success', 'Pengaturan RKAT berhasil diupdate!');
        $this->dispatchBrowserEvent('pengaturan-rkat.data-saved');
    }

    protected function defaultValuesPengaturanRKAT(): void
    {
        $settings = app(RKATSettings::class);

        $this->tahunRKAT = $settings->tahun;
        $this->tglAwalPenetapanRKAT = $settings->tgl_penetapan_awal->format('Y-m-d');
        $this->tglAkhirPenetapanRKAT = $settings->tgl_penetapan_akhir->format('Y-m-d');
    }
}
