<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('export_sessions', function (Blueprint $table): void {
            $table->id();
            $table->char('session_id', 36)->unique();
            $table->string('id_user');
            $table->string('export_name');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps($precision = 6);
        });
    }
};
