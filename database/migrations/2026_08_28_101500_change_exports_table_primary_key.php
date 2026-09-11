<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mengganti primary key exports dari `id` AUTO_INCREMENT menjadi gabungan
     * (export_session_id, id), dengan `id` sebagai nomor urut per sesi export.
     *
     * Dengan innodb_autoinc_lock_mode = 1, INSERT ... SELECT dihitung sebagai
     * bulk insert sehingga InnoDB memegang AUTO-INC lock setingkat tabel selama
     * statement berjalan. Pada export yang memakan waktu menit, seluruh export
     * lain ikut antre. Nomor urut per sesi dibuat lewat row_number() di dalam
     * statement yang sama, jadi tidak ada AUTO-INC lock dan tidak perlu tabel
     * counter.
     *
     * Efek sampingnya, baris milik satu sesi menjadi berdekatan pada clustered
     * index, sehingga chunkById dan lookup `IN (...)` berubah dari lompat-lompat
     * menjadi pembacaan berurutan.
     */
    public function up(): void
    {
        $connection = DB::connection('mysql_smc');

        // Isi tabel ini hanya data antara milik export yang sedang berjalan dan
        // selalu dihapus setelah selesai, jadi aman dikosongkan.
        $connection->statement('truncate table exports');

        // AUTO_INCREMENT wajib dilepas lebih dulu, karena MariaDB mensyaratkan
        // kolom AUTO_INCREMENT selalu menjadi bagian dari sebuah key.
        $connection->statement('alter table exports modify id bigint unsigned not null');

        $connection->statement('alter table exports drop primary key, add primary key (export_session_id, id)');

        // idx_export_session menjadi mubazir: export_session_id sudah menjadi
        // kolom terkiri pada primary key yang baru.
        $connection->statement('alter table exports drop index idx_export_session');

        // Kedua DELETE pada alur export menyaring id_user + export_name.
        // Indeks lama hanya mencakup id_user, sehingga rentang kuncinya melebar
        // ke seluruh baris milik user tersebut, termasuk milik export lain.
        $connection->statement('alter table exports drop index idx_user_export');
        $connection->statement('alter table exports add index idx_user_export (id_user, export_name)');

        /*
         * created_at NOT NULL tanpa default, sementara INSERT ... SELECT pada
         * PrepareExport tidak pernah mengisinya. Selama
         * explicit_defaults_for_timestamp = 0, MariaDB diam-diam memberi
         * DEFAULT CURRENT_TIMESTAMP pada kolom TIMESTAMP pertama sehingga
         * kelolosan ini tidak terlihat; begitu server disetel ke 1, insert yang
         * sama gagal dengan error 1364 di bawah STRICT_TRANS_TABLES.
         *
         * Kolomnya sendiri tidak pernah dibaca maupun ditulis oleh siapa pun:
         * ExportCsv hanya menyeleksi column1..column9 dan WriteExcel hanya
         * menghapus baris. Pasangan updated_at juga tidak pernah ada, jadi
         * kolom ini dihapus saja alih-alih diberi default.
         */
        $connection->statement('alter table exports drop column created_at');

        /*
         * columnN dibuat varchar(255), padahal kolom sumber bisa lebih panjang:
         * sik.jurnal.keterangan saja varchar(350) dan isi terpanjangnya 342
         * karakter. Di bawah STRICT_TRANS_TABLES kelebihan itu bukan warning
         * melainkan error 1406, sehingga export gagal di tengah jalan. Tabel
         * ini dipakai bersama oleh export mana pun, jadi seluruh slot
         * dilebarkan sekaligus agar tetap generik. varchar panjangnya variabel,
         * jadi nilai pendek tidak ikut membesar.
         */
        $connection->statement(<<<'SQL'
            alter table exports
            modify column1 varchar(500) null,
            modify column2 varchar(500) null,
            modify column3 varchar(500) null,
            modify column4 varchar(500) null,
            modify column5 varchar(500) null,
            modify column6 varchar(500) null,
            modify column7 varchar(500) null,
            modify column8 varchar(500) null,
            modify column9 varchar(500) null,
            modify column10 varchar(500) null,
            modify column11 varchar(500) null,
            modify column12 varchar(500) null,
            modify column13 varchar(500) null,
            modify column14 varchar(500) null,
            modify column15 varchar(500) null,
            modify column16 varchar(500) null,
            modify column17 varchar(500) null,
            modify column18 varchar(500) null,
            modify column19 varchar(500) null,
            modify column20 varchar(500) null
            SQL);
    }
};
