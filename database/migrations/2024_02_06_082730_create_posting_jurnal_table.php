<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('posting_jurnal', function (Blueprint $table): void {
            $table->id();
            $table->string('no_jurnal', 20)->nullable()->index();
            $table->date('tgl_jurnal')->nullable()->index();
            $table->timestamps($precision = 6);
        });
    }
};
