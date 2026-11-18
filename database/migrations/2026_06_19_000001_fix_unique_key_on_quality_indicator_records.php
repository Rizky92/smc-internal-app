<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_records', function (Blueprint $table): void {
            $table->dropUnique('quality_indicator_records_recorded_date_unique');
            $table->unique(['indicator_id', 'recorded_date'], 'quality_indicator_records_indicator_date_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_records', function (Blueprint $table): void {
            $table->dropUnique('quality_indicator_records_indicator_date_unique');
            $table->unique('recorded_date', 'quality_indicator_records_recorded_date_unique');
        });
    }
};
