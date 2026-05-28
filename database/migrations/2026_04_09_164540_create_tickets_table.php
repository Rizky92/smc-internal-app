<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('tickets', function (Blueprint $table): void {
            $table->id();

            // Nomor tiket yang tampil di UI, format: ITS-XXXX
            $table->string('ticket_number', 20)->unique();

            $table->string('title');
            $table->text('description')->nullable();

            // Kategori: SW, HW, NET, INST, SEC, OTHER
            $table->foreignId('category_id')
                ->constrained('ticket_categories')
                ->restrictOnDelete();

            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');

            $table->enum('status', [
                'open',       // Baru dibuat, belum diproses
                'progress',   // Sedang dikerjakan
                'waiting',    // Menunggu konfirmasi / part / pihak ketiga
                'resolved',   // Selesai, menunggu verifikasi pelapor
                'closed',     // Ditutup
            ])->default('open');

            // Departemen / unit yang melaporkan
            $table->foreignId('department_id')
                ->constrained('bidang')
                ->restrictOnDelete();

            // Lokasi spesifik, misal: "Nurse Station Lt.3", "Komp. No.4 Poli Anak"
            $table->string('location')->nullable();

            // Pelapor bisa user terdaftar atau orang luar (tamu/non-akun)
            $table->string('reporter_id')->nullable()->index();

            // Untuk pelapor walk-in / tidak punya akun
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 20)->nullable();

            // Teknisi yang mengerjakan
            $table->string('assignee_id')->nullable()->index();

            // User yang membuat tiket (bisa admin/staff)
            $table->string('created_by')->nullable()->index();

            // SLA tracking
            $table->timestamp('sla_due_at')->nullable();          // Deadline resolusi
            $table->timestamp('sla_response_due_at')->nullable(); // Deadline first response
            $table->timestamp('first_responded_at')->nullable();  // Waktu respons pertama
            $table->timestamp('sla_breached_at')->nullable();     // Kapan SLA dilanggar (null = belum)

            // Lifecycle timestamps
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk query umum
            $table->index('status');
            $table->index('priority');
            $table->index('department_id');
            $table->index('created_at');
            $table->index('sla_due_at');
        });
    }
};
