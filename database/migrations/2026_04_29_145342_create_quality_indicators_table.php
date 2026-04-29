<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('quality_indicators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unit_id')->constrained('service_units');
            $table->foreignId('category_id')->constrained('indicator_categories');
            $table->integer('sort_order');
            $table->text('title');
            $table->text('dimension')->nullable();
            $table->text('objective')->nullable();
            $table->text('definition')->nullable();
            $table->text('inclusion')->nullable();
            $table->text('exclusion')->nullable();
            $table->string('frequency', 45);
            $table->foreignId('input_type_id')->nullable()->constrained('indicator_input_types');
            $table->integer('analysis_period')->nullable();
            $table->text('numerator')->nullable();
            $table->text('denominator')->nullable();
            $table->string('data_source', 200)->nullable();
            $table->string('standard', 100);
            $table->string('person_in_charge', 200)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps($precision = 6);
        });
    }
};
