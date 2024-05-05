<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->table('nota_selesai', function (Blueprint $table): void {
            $table->string('status_bayar', 10)->after('bentuk_bayar');
        });
    }
};
