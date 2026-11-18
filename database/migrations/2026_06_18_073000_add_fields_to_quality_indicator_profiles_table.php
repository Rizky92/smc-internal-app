<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_profiles', function (Blueprint $table): void {
            $table->text('rationale')->nullable()->after('standard');
            $table->string('indicator_type', 100)->nullable()->after('rationale');
            $table->string('measurement_unit', 100)->nullable()->after('indicator_type');
            $table->text('formula')->nullable()->after('measurement_unit');
            $table->text('data_collection_method')->nullable()->after('formula');
            $table->text('instrument')->nullable()->after('data_collection_method');
            $table->string('sample_size', 100)->nullable()->after('instrument');
            $table->text('sampling_method')->nullable()->after('sample_size');
            $table->text('data_presentation')->nullable()->after('sampling_method');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'rationale',
                'indicator_type',
                'measurement_unit',
                'formula',
                'data_collection_method',
                'instrument',
                'sample_size',
                'sampling_method',
                'data_presentation',
            ]);
        });
    }
};
