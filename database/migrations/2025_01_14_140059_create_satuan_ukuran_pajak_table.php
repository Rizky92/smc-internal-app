<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('satuan_ukuran_pajak', function (Blueprint $table): void {
            $table->string('kode_satuan_pajak', 15)->primary();
            $table->string('nama_satuan_pajak', 50);
            $table->char('kode_sat', 4)->nullable()->index();
        });
    }
};
