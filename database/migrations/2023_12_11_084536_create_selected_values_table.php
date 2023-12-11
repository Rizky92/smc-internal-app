<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selected_values', function (Blueprint $table): void {
            $table->id();
            $table->string('keys')->unique();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('created_at')->nullable();
        });
    }
};
