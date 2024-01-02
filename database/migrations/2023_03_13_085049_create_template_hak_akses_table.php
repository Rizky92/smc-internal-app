<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('template_hak_akses', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->timestamps($precision = 6);
        });
    }
};
