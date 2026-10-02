<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::create('akreditasi_assessment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_element_id')->constrained('akreditasi_assessment_elements')->cascadeOnDelete();
            $table->string('judul_dokumen');
            $table->string('keterangan')->nullable();
            $table->string('file_path');
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akreditasi_assessment_documents');
    }
};
