<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('ticket_attachments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->string('id_user')->nullable()->index();

            $table->string('original_name');       // Nama file asli
            $table->string('path');                // Path di storage
            $table->string('disk', 20)->default('public');
            $table->unsignedBigInteger('size');    // Ukuran dalam bytes
            $table->string('mime_type', 100)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('ticket_id');
        });
    }
};
