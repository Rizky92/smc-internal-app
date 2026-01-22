<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('exports', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->char('export_session_id', 36);
            $table->string('id_user');
            $table->string('column1')->nullable();
            $table->string('column2')->nullable();
            $table->string('column3')->nullable();
            $table->string('column4')->nullable();
            $table->string('column5')->nullable();
            $table->string('column6')->nullable();
            $table->string('column7')->nullable();
            $table->string('column8')->nullable();
            $table->string('column9')->nullable();
            $table->string('column10')->nullable();
            $table->string('column11')->nullable();
            $table->string('column12')->nullable();
            $table->string('column13')->nullable();
            $table->string('column14')->nullable();
            $table->string('column15')->nullable();
            $table->string('column16')->nullable();
            $table->string('column17')->nullable();
            $table->string('column18')->nullable();
            $table->string('column19')->nullable();
            $table->string('column20')->nullable();
            $table->timestamp('created_at', 6);

            $table->index('export_session_id', 'idx_export_session');
            $table->index(['id_user', 'export_session_id'], 'idx_user_export');
        });
    }
};
