# 0003: MySQL sebagai validator Import Tarif; pre-check hanya untuk aturan bisnis

- Status: accepted
- Date: 2026-10-07

## Context

Import Tarif Operasi gagal dengan `Baris 228: Gagal menyimpan data ke database. Pastikan format data sudah benar.` Kelima job Import Tarif (Lab, Radiologi, Ralan, Ranap, Operasi) menangkap `QueryException` dan membuang alasan aslinya, sehingga pengguna tidak tahu sel mana yang salah. Penyebabnya bisa nilai enum di luar pilihan (Kelas, Kategori), teks terlalu panjang, atau sel kosong pada kolom NOT NULL.

Ada dua cara memberi pesan yang jelas:

- Memvalidasi setiap kolom di job sebelum menyimpan. Pilihan enum dan panjang kolom harus disalin dari skema Khanza ke kode PHP, dan bisa tidak sinkron kalau Khanza mengubah skemanya.
- Membiarkan MySQL menolak baris yang salah, lalu menerjemahkan errornya. Koneksi `mysql_sik` sudah strict: `'modes'` di `config/database.php` memuat `STRICT_TRANS_TABLES`, dan Laravel men-set `sql_mode` sesi dari situ (bila `'modes'` diisi, nilai `'strict'` diabaikan). Selain itu kelima tabel tujuan InnoDB, jadi error pasti muncul dan transaksi bisa di-rollback.

## Decision

MySQL menjadi validator untuk aturan yang sudah ada di skema (enum/set, panjang, NOT NULL, tipe, foreign key, unique):

- `App\Support\QueryErrorTranslator` menerjemahkan `QueryException` berdasarkan `errorInfo[1]` (1265, 1406, 1048, 1366, 1452, 1062) menjadi pesan dengan header Excel dan nilai yang diketik pengguna, plus `(kode N)`. Pilihan enum dan panjang maksimum dibaca dari `information_schema` saat error, bukan disalin ke kode. Kode lain mendapat pesan "coba ulangi; jika masih gagal, hubungi tim IT" beserta kodenya.
- Job melempar `ImportTarifException($pesan, 0, $queryException)`, jadi notifikasi berisi pesan yang bisa dibaca dan log tetap menyimpan SQL aslinya.
- Pre-check di job hanya untuk aturan yang tidak diketahui skema: Jenis Bayar harus aktif, kecocokan total biaya (Lab, Radiologi, Ralan, Ranap), dan master data Kategori/Poli/Bangsal (Ralan, Ranap).
- Parsing nominal tetap di PHP (`parse_numeric()`), karena teks seperti `1.500` atau `Rp 1.500` harus diubah sebelum disimpan. Format yang tidak jelas ditolak dengan pesan "harus berupa angka".
- Bagian bersama kelima job (nomor baris sesuai Excel, baris kosong, parsing nominal, terjemahan error) ada di trait `App\Jobs\Keuangan\Concerns\ImportsTarifRows`, sesuai klausul "focused class" di ADR 0001.

## Consequences

- Pesan error mengikuti skema Khanza tanpa daftar pilihan yang perlu dirawat di kode.
- Import berhenti di error pertama: satu baris, dan satu kolom per baris (MySQL hanya melaporkan kolom pertama yang gagal). Pengguna memperbaiki lalu mengimpor ulang.
- Perilaku bergantung pada strict mode. Kalau `STRICT_TRANS_TABLES` dihapus dari `'modes'` `mysql_sik` (atau `'modes'` dihapus lalu `'strict'` dimatikan), MySQL akan memotong atau mengosongkan nilai tanpa error, dan validasi ini hilang diam-diam.
- Pesan 1366/1265 bergantung pada format teks error MySQL/MariaDB. Translator mendukung format MariaDB 10.4 dan MySQL 8. Format yang tidak dikenali jatuh ke pesan fallback, bukan error.
