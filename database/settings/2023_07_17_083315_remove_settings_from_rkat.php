<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('rkat', function (SettingsBlueprint $settings): void {
            $settings->delete('batas_input_awal', now()->startOfYear());
            $settings->delete('batas_input_akhir', now()->endOfYear());
        });
    }
};
