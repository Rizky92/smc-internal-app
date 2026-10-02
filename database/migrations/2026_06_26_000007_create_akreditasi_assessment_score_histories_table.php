<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::create('akreditasi_assessment_score_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_element_id');
            $table->foreign('assessment_element_id', 'ash_ae_fk')->references('id')->on('akreditasi_assessment_elements')->onDelete('cascade');
            $table->string('skor_lama')->nullable();
            $table->string('skor_baru')->nullable();
            $table->text('catatan')->nullable();
            $table->string('changed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasi_assessment_score_histories');
    }
};
