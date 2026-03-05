<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('export_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('status');
            $table->unsignedInteger('total_jobs')->default(0);
            $table->unsignedInteger('completed_jobs')->default(0);
            $table->string('file_path')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps($precision = 6);

            $table->index(['user_id', 'status']);
        });
    }
};
