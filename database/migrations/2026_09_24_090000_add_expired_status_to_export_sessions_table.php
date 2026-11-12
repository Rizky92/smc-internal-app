<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Status `expired` menandai export yang file-nya sudah dihapus oleh
     * exports:clean karena melewati masa simpan.
     */
    public function up(): void
    {
        DB::connection('mysql_smc')->statement(
            "alter table export_sessions modify status enum('pending', 'processing', 'completed', 'failed', 'expired') not null default 'pending'"
        );
    }

    public function down(): void
    {
        DB::connection('mysql_smc')->table('export_sessions')
            ->where('status', 'expired')
            ->update(['status' => 'failed']);

        DB::connection('mysql_smc')->statement(
            "alter table export_sessions modify status enum('pending', 'processing', 'completed', 'failed') not null default 'pending'"
        );
    }
};
