<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NIK pegawai PIC indikator. Pegawai ada di mysql_sik, jadi tanpa foreign key.
     */
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->string('pic_nik', 50)->nullable()->after('person_in_charge');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->dropColumn('pic_nik');
        });
    }
};
