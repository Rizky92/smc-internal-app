<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('anggaran', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps($precision = 6);
            $table->softDeletes('deleted_at', $precision);
        });
    }
};
