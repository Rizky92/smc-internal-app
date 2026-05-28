<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('ticket_comments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            $table->string('id_user')->nullable()->index();

            $table->text('content');

            // is_internal: true = catatan internal IT (tidak terlihat pelapor)
            $table->boolean('is_internal')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_id');
        });
    }
};
