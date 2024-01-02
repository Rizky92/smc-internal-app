<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('rkat', function (SettingsBlueprint $settings): void {
            $settings->add('tahun', now()->format('Y'));
            $settings->add('tgl_penetapan_awal', carbon('2022-10-01'));
            $settings->add('tgl_penetapan_akhir', carbon('2022-11-30'));
        });
    }
};
