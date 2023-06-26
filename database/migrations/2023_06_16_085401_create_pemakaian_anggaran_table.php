<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var ?string
     */
    protected $connection = 'mysql_smc';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('pemakaian_anggaran_bidang', function (Blueprint $table): void {
            $table->id();
            $table->text('deskripsi')->nullable();
            $table->bigInteger('nominal_pemakaian');
            $table->date('tgl_dipakai');
            $table->foreignId('anggaran_bidang_id')->constrained('anggaran_bidang');
            $table->foreignId('user_id');
            $table->timestamps($precision = 6);
        });
    }
};
