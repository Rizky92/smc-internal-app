<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->dropForeign(['bidang_id']);
        });

        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->renameColumn('bidang_id', 'dep_id');
        });

        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->string('dep_id', 50)->change();
        });

        Schema::connection('mysql_smc')->dropIfExists('indikator_jabatan_unit');
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->create('indikator_jabatan_unit', function (Blueprint $table): void {
            $table->id();
            $table->string('jabatan_id', 50);
            $table->unsignedBigInteger('unit_id');
            $table->timestamps();
            $table->unique(['jabatan_id', 'unit_id']);
        });

        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->unsignedBigInteger('dep_id')->change();
        });

        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->renameColumn('dep_id', 'bidang_id');
        });

        Schema::connection('mysql_smc')->table('quality_indicators', function (Blueprint $table): void {
            $table->foreign('bidang_id')->references('id')->on('bidang');
        });
    }
};
