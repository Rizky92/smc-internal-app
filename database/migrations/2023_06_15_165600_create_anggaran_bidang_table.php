<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('anggaran_bidang', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran');
            $table->foreignId('bidang_id')->constrained('bidang');
            $table->year('tahun');
            $table->double('nominal_anggaran');
            $table->timestamps($precision = 6);

            $table->unique(['anggaran_id', 'bidang_id', 'tahun']);
        });
    }
};
