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
        Schema::connection('mysql_smc')->table('pemakaian_anggaran_bidang', function (Blueprint $table): void {
            $table->foreignId('user_id')->after('anggaran_bidang_id');
        });
    }
};
