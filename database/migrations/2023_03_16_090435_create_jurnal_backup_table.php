<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'mysql_smc';

    public function up(): void
    {
        Schema::connection('mysql_smc')->create('jurnal_backup', function (Blueprint $table): void {
            $table->id();
            $table->string('no_jurnal', 20)->nullable()->index();
            $table->date('tgl_jurnal_asli')->nullable()->index();
            $table->date('tgl_jurnal_diubah')->nullable()->index();
            $table->string('nip', 20)->nullable()->index();
            $table->timestamps($precision = 6);
        });
    }
};
