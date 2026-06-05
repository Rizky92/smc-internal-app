<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('quality_indicators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidang');
            $table->string('data_source', 200)->nullable();
            $table->string('person_in_charge', 200)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps($precision = 6);
        });
    }
};
