<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('faktur_pajak', function (SettingsBlueprint $settings): void {
            $settings->add('npwp_penjual', '');
        });
    }
};
