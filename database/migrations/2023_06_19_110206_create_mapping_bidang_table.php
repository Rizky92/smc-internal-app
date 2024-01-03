<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('mapping_bidang', function (Blueprint $table): void {
            $table->foreignId('bidang_id');
            $table->foreignId('kd_bangsal');
        });
    }
};
