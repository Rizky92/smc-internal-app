<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('khanza_mapping_akses', function (Blueprint $table): void {
            $table->string('nama_field', 100);
            $table->string('judul_menu', 100)->nullable();
            $table->enum('default_value', ['true', 'false'])->default('false');
            $table->primary('nama_field');
            $table->index('judul_menu');
        });
    }
};
