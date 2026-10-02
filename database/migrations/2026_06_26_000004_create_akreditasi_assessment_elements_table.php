<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::create('akreditasi_assessment_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_id')->constrained('akreditasi_standards')->cascadeOnDelete();
            $table->string('kode', 20);
            $table->text('deskripsi');
            $table->text('penjelasan_kelengkapan_bukti')->nullable();
            $table->foreignId('proof_method_id')->nullable()->constrained('akreditasi_proof_methods')->nullOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['standard_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasi_assessment_elements');
    }
};
