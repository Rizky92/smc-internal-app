<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('quality_indicator_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quality_indicator_category_id')->constrained('quality_indicator_categories');
            $table->unsignedBigInteger('quality_indicator_input_type_id')->nullable();
            $table->foreign('quality_indicator_input_type_id', 'qi_profiles_input_type_id_fk')->references('id')->on('quality_indicator_input_types');
            $table->text('title');
            $table->text('dimension')->nullable();
            $table->text('objective')->nullable();
            $table->text('definition')->nullable();
            $table->text('inclusion')->nullable();
            $table->text('exclusion')->nullable();
            $table->string('frequency', 45);
            $table->integer('analysis_period')->nullable();
            $table->text('numerator')->nullable();
            $table->text('denominator')->nullable();
            $table->string('standard', 100);
            $table->timestamps($precision = 6);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->dropIfExists('quality_indicator_profiles');
    }
};
