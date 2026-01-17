<?php

namespace App\Livewire\Pages\Aplikasi\Concerns;

use App\Settings\AntreanLoketSettings;

trait PengaturanAntreanLoket
{
    /** @var bool */
    public $antreanPrefixHuruf;

    /** @var array */
    public $prefixHurufAktif;

    public static function getPengaturanAntreanLoketPermissions(): array
    {
        return [
            'aplikasi.pengaturan-antrean-loket.read',
            'aplikasi.pengaturan-antrean-loket.update',
        ];
    }

    public function mountPengaturanAntreanLoket(): void
    {
        $this->defaultValuesPengaturanAntreanLoket();
    }

    public function getStatusAntreanPrefixHurufProperty(): array
    {
        return [
            true  => 'Ya',
            false => 'Tidak',
        ];
    }

    public function getDataPrefixHurufAktifProperty(): array
    {
        return AntreanLoketSettings::PREFIX_HURUF_OPTIONS;
    }

    public function updatePengaturanAntreanLoket(): void
    {
        if (user()->cannot('aplikasi.pengaturan-antrean-loket.update')) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('pengaturan-antrean-loket.data-denied');

            return;
        }

        // Normalize property first: Livewire may set "0"/"1" strings on the public property.
        $this->antreanPrefixHuruf = filter_var($this->antreanPrefixHuruf, FILTER_VALIDATE_BOOLEAN);

        $validated = $this->validate([
            'antreanPrefixHuruf'  => ['required', 'boolean'],
            'prefixHurufAktif'    => ['nullable', 'array'],
            'prefixHurufAktif.*'  => ['in:A,B,C,D,E,F'],
        ]);

        tracker_start();

        app(AntreanLoketSettings::class)
            ->fill([
                'antrean_prefix_huruf' => $validated['antreanPrefixHuruf'],
                'prefix_huruf_aktif'   => $validated['prefixHurufAktif'] ?? [],
            ])
            ->save();

        app(AntreanLoketSettings::class)->refresh();

        tracker_end();

        $this->emit('flash.success', 'Pengaturan antrean loket berhasil diperbarui!');
        $this->dispatchBrowserEvent('pengaturan-antrean-loket.data-updated');
    }

    public function defaultValuesPengaturanAntreanLoket(): void
    {
        $settings = app(AntreanLoketSettings::class);

        $this->antreanPrefixHuruf = $settings->antrean_prefix_huruf;
        $this->prefixHurufAktif = $settings->prefix_huruf_aktif ?? [];
    }
}
