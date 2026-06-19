<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('indikator_harian_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('indicator_id');
            $table->date('recorded_date');
            $table->string('field_name', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('changed_by', 50)->nullable();
            $table->text('reason');
            $table->timestamp('created_at', 6)->nullable();

            $table->foreign('indicator_id', 'audit_logs_indicator_fk')
                ->references('id')
                ->on('quality_indicators')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->dropIfExists('indikator_harian_audit_logs');
    }
};
