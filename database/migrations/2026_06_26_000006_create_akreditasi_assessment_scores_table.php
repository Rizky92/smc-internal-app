<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::create('akreditasi_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_element_id')->constrained('akreditasi_assessment_elements')->cascadeOnDelete();
            $table->string('skor')->nullable();
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->string('assessed_by')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasi_assessment_scores');
    }
};
