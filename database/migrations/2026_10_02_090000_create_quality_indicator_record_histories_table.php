<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('quality_indicator_record_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('record_id')->constrained('quality_indicator_records')->cascadeOnDelete();
            $table->string('action', 50);
            $table->string('status_before', 50)->nullable();
            $table->string('status_after', 50);
            $table->integer('numerator_value');
            $table->integer('denominator_value');
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->string('actor', 50)->nullable();
            $table->timestamp('created_at', 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->dropIfExists('quality_indicator_record_histories');
    }
};
