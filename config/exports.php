<?php

return [
    /*
     * Disk tempat semua artefak export ditulis. Jalur background export
     * (PrepareExport/ExportCsv/WriteExcel) maupun export sinkron (ExcelExportJob
     * dan trait ExcelExportable) sama-sama memakai disk ini.
     */
    'disk' => env('EXPORT_DISK', 'public'),

    'directories' => [
        // Hasil PrepareExport/ExportCsv/WriteExcel: exports/{nik}/{sessionId}/
        'background' => 'exports',

        // Hasil ExcelExportJob & trait ExcelExportable (basePath default xlswriter)
        'sync' => 'excel',
    ],

    'cleanup' => [
        /*
         * Umur minimum sebuah sesi export sebelum boleh dibersihkan. Cleanup
         * berjalan tanpa memandang status sesi supaya sesi yang macet di
         * pending/processing ikut terbersihkan, jadi masa tenggang inilah
         * satu-satunya pengaman agar export yang sedang berjalan tidak ikut
         * terhapus. Jangan turunkan di bawah timeout job (3600 detik).
         */
        'grace_hours' => env('EXPORT_CLEANUP_GRACE_HOURS', 24),

        // Jumlah baris/file yang diproses per batch penghapusan.
        'chunk' => 500,
    ],

    /*
     * Cleanup notifikasi hanya menyentuh notifikasi export, bukan seluruh isi
     * tabel notifications. Sebuah notifikasi dianggap milik export kalau pesannya
     * berawalan salah satu message_prefixes, atau file lampirannya berawalan
     * salah satu file_prefixes.
     */
    'notification' => [
        'message_prefixes' => ['Export'],

        'file_prefixes' => ['exports/', 'excel/'],
    ],
];
