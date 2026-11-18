<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('quality_indicator_correction_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('record_id')->constrained('quality_indicator_records')->cascadeOnDelete();
            $table->integer('numerator_value');
            $table->integer('denominator_value');
            $table->text('notes')->nullable();
            $table->text('reason');
            $table->string('requested_by', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('reviewed_by', 50)->nullable();
            $table->text('review_reason')->nullable();
            $table->timestamp('created_at', 6)->nullable();
            $table->timestamp('updated_at', 6)->nullable();

            $table->index(['record_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->dropIfExists('quality_indicator_correction_requests');
    }
};
