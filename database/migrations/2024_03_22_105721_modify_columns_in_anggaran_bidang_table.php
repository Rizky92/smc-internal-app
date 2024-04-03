<?php

use App\Models\Keuangan\RKAT\AnggaranBidang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->disableForeignKeyConstraints();

        AnggaranBidang::truncate();

        Schema::connection('mysql_smc')->table('anggaran_bidang', function (Blueprint $table): void {
            $table->dropColumn('nama_kegiatan');
            $table->dropColumn('deskripsi');

            $table->unique(['anggaran_id', 'bidang_id', 'tahun']);
        });

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
    }
};
