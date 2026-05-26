<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('quality_indicator_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('indicator_id')->constrained('quality_indicators');
            $table->date('recorded_date')->unique();
            $table->text('notes')->nullable();
            $table->integer('numerator_value')->default(0);
            $table->integer('denominator_value')->default(0);
            $table->string('recorded_by')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('created_at', 6)->nullable();
            $table->timestamp('updated_at', 6)->nullable();
        });
    }
};
