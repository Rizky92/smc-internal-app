<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->foreignId('quality_indicator_profile_id')
                ->after('id')
                ->nullable()
                ->constrained('quality_indicator_profiles');
        });
    }
};
