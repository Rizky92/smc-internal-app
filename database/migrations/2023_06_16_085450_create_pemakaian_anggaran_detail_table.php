<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('pemakaian_anggaran_detail', function (Blueprint $table): void {
            $table->id();
            $table->string('keterangan');
            $table->double('nominal');
            $table->foreignId('pemakaian_anggaran_id')->constrained('pemakaian_anggaran');
            $table->timestamps($precision = 6);
        });
    }
};
