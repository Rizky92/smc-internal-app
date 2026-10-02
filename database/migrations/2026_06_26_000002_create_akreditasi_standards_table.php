<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::create('akreditasi_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('focus_area_id')->constrained('akreditasi_focus_areas')->cascadeOnDelete();
            $table->string('kode', 20);
            $table->string('judul');
            $table->text('maksud_tujuan')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['focus_area_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasi_standards');
    }
};
