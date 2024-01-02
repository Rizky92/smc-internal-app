<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('anggaran_bidang', function (Blueprint $table): void {
            $table->dropUnique(['anggaran_id', 'bidang_id', 'tahun']);
        });

        Schema::enableForeignKeyConstraints();
    }
};
