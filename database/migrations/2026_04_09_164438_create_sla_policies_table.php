<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('sla_policies', function (Blueprint $table): void {
            $table->id();
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->unique();
            $table->unsignedSmallInteger('response_hours');
            $table->unsignedSmallInteger('resolution_hours');
            $table->timestamps();
        });
    }
};
