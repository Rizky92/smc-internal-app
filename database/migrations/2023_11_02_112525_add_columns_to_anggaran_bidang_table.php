<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggaran_bidang', function (Blueprint $table): void {
            $table->string('nama_kegiatan')
                ->after('bidang_id');

            $table->text('deskripsi')
                ->nullable()
                ->after('nama_kegiatan');
        });
    }
};
