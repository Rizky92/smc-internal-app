<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('rkat', function (SettingsBlueprint $settings): void {
            $settings->add('batas_input_awal', now()->startOfYear());
            $settings->add('batas_input_akhir', now()->endOfYear());

            $settings->rename('tgl_awal', 'batas_penetapan_awal');
            $settings->rename('tgl_akhir', 'batas_penetapan_akhir');
        });
    }
};
