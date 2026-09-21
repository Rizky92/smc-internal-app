<?php

namespace App\Livewire\Pages\Aplikasi\Concerns;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;

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
        // This year and next, so the Tahun RKAT can be advanced before any
        // Penetapan RKAT exists for it.
        return AnggaranBidang::pilihanTahun(now()->year, now()->year + 1);
    }

    public function updatePengaturanRKAT(): void
    {
        if (user()->cannot('aplikasi.pengaturan-rkat.update')) {
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('pengaturan-rkat.data-denied');

            return;
        }

        $validated = $this->validate([
            'tahunRKAT'              => ['required'],
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

        app(RKATSettings::class)->refresh();

        tracker_end();

        $this->dispatch('flash.success', 'Pengaturan RKAT berhasil diupdate!');
        $this->dispatch('pengaturan-rkat.data-saved');
    }

    protected function defaultValuesPengaturanRKAT(): void
    {
        $settings = app(RKATSettings::class);

        $this->tahunRKAT = $settings->tahun;
        $this->tglAwalPenetapanRKAT = $settings->tgl_penetapan_awal->toDateString();
        $this->tglAkhirPenetapanRKAT = $settings->tgl_penetapan_akhir->toDateString();
    }
}
