<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('mark_email_sent', function (Blueprint $table): void {
            $table->id();
            $table->string('no_bukti')->nullable()->index();
            $table->string('email');
            $table->timestamp('sent_at', $precision = 6);
        });
    }
};
