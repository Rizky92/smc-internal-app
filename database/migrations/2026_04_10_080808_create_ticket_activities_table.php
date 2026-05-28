<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('ticket_activities', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            // Siapa yang melakukan aksi (null = sistem / otomatis)
            $table->string('causer_id')->nullable()->index();

            $table->enum('type', [
                'created',          // Tiket dibuat
                'assigned',         // Teknisi di-assign
                'status_changed',   // Status berubah
                'priority_changed', // Prioritas berubah
                'sla_breached',     // SLA dilanggar (otomatis)
                'note_added',       // Catatan internal
                'escalated',        // Dieskalasi ke atasan
            ]);

            $table->string('description');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('ticket_id');
            $table->index('created_at');
        });
    }
};
