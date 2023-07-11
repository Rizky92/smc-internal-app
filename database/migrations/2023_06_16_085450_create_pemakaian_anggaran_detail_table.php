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
        Schema::connection('mysql_smc')->create('pemakaian_anggaran_detail', function (Blueprint $table): void {
            $table->id();
            $table->string('keterangan');
            $table->double('nominal');
            $table->foreignId('pemakaian_anggaran_id')->constrained('pemakaian_anggaran');
            $table->timestamps($precision = 6);
        });
    }
};
