<?php

namespace App\Livewire\Pages\Aplikasi\Concerns;

use App\Settings\NPWPSettings;

trait SetNPWPPenjual
{
    /** @var string */
    public $npwpPenjual;

    public static function getSetNPWPPenjualPermissions(): array
    {
        return [
            'aplikasi.set-npwp-penjual.update',
        ];
    }

    public function mountSetNPWPPenjual(): void
    {
        $this->defaultValuesSetNPWPPenjual();
    }

    public function updateNPWPPenjual(): void
    {
        if (user()->cannot('aplikasi.set-npwp-penjual.update')) {
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatch('set-npwp-penjual.data-denied');

            return;
        }

        $validated = $this->validate([
            'npwpPenjual' => ['required'],
        ]);

        tracker_start();

        app(NPWPSettings::class)
            ->fill(['npwp_penjual' => $validated['npwpPenjual']])
            ->save();

        app(NPWPSettings::class)->refresh();

        tracker_end();

        $this->dispatch('flash.success', 'Pengaturan NPWP Penjual berhasil diupdate!');
        $this->dispatch('set-npwp-penjual.data-saved');
    }

    protected function defaultValuesSetNPWPPenjual(): void
    {
        $settings = app(NPWPSettings::class);

        $this->npwpPenjual = $settings->npwp_penjual;
    }
}
