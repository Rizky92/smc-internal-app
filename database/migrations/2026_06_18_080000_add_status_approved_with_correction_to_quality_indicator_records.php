<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_records', function (Blueprint $table): void {
            $table->string('status', 50)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicator_records', function (Blueprint $table): void {
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->change();
        });
    }
};
