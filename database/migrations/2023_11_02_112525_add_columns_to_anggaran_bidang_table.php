<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->disableForeignKeyConstraints();

        Schema::connection('mysql_smc')->table('anggaran_bidang', function (Blueprint $table): void {
            $table->string('nama_kegiatan')
                ->after('bidang_id');

            $table->text('deskripsi')
                ->nullable()
                ->after('nama_kegiatan');

            $table->dropUnique(['anggaran_id', 'bidang_id', 'tahun']);
        });

        Schema::connection('mysql_smc')->enableForeignKeyConstraints();
    }
};
