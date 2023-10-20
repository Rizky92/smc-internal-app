<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_hak_akses_khanza_detail', function (Blueprint $table): void {
            $table->foreignId('template_id')->constrained('template_hak_akses_khanza', 'id');
            $table->string('hak_akses')->index();
            $table->string('value')->default('false');
        });
    }
};
