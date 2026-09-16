<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_smc')->create('dicom_router_webhook_logs', function (Blueprint $table): void {
            $table->id();

            /*
             * Sidik jari isi payload. DICOM Router tidak mengirim id kiriman
             * dan tombol resend di dashboard-nya mengirim ulang body yang
             * persis sama, jadi hash inilah yang membuat handler idempotent:
             * resend menaikkan `delivery_count`, bukan menambah baris baru.
             */
            $table->char('signature', 40)->unique();

            $table->boolean('status')->default(false);
            $table->string('stage', 50);
            $table->string('message', 255)->nullable();

            $table->string('organization_id', 50)->nullable();
            $table->string('imaging_study_id', 64)->nullable();
            $table->string('accession_number', 20)->nullable();

            /*
             * Diturunkan dari accession number, bukan dikirim router. Dipakai
             * untuk menyambung ke permintaan_radiologi di database Khanza.
             */
            $table->string('noorder', 15)->nullable();

            $table->string('study_instance_uid', 64)->nullable();

            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();

            /*
             * Body mentah apa adanya. Studi USG obstetri menyisipkan 13 key
             * hasil ukur SR ke dalam `data`, dan router membuang key bernilai
             * null, jadi bentuk payload tidak tetap dan tidak bisa dipetakan
             * seluruhnya ke kolom.
             */
            $table->json('payload');

            $table->unsignedInteger('delivery_count')->default(1);

            $table->timestamps($precision = 6);

            $table->index('stage');
            $table->index('noorder');
            $table->index('accession_number');
            $table->index('imaging_study_id');
            $table->index('study_instance_uid');
            $table->index('created_at');
        });
    }
};
