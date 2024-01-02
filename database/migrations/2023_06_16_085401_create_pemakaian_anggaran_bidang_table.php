<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('pemakaian_anggaran', function (Blueprint $table): void {
            $table->id();
            $table->string('judul')->nullable();
            $table->text('deskripsi')->nullable();
            $table->date('tgl_dipakai');
            $table->foreignId('anggaran_bidang_id')->constrained('anggaran_bidang');
            $table->string('user_id', 20)->index();
            $table->timestamps($precision = 6);
        });
    }
};
