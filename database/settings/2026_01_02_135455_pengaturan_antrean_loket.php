<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('antrean_loket', function (SettingsBlueprint $settings): void {
            $settings->add('antrean_prefix_huruf', true);
            $settings->add('prefix_huruf_aktif', ['A', 'B', 'C', 'D', 'E', 'F']);
        });
    }
};
