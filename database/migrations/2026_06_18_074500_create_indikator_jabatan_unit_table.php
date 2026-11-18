<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('indikator_jabatan_unit', function (Blueprint $table): void {
            $table->id();
            $table->string('jabatan_id', 50)->index();
            $table->unsignedBigInteger('unit_id');
            $table->timestamps();

            $table->unique(['jabatan_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_smc')->dropIfExists('indikator_jabatan_unit');
    }
};
