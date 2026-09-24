<?php

return [
    /*
     * Lama file hasil export disimpan sebelum dihapus oleh exports:clean.
     */
    'retention_hours' => (int) env('EXPORT_RETENTION_HOURS', 24),

    /*
     * Folder file sementara OpenSpout selama menyusun xlsx. XML seluruh sheet
     * ditampung di sini sampai file di-zip, jadi di server sebaiknya diarahkan
     * ke tmpfs agar tidak menambah beban tulis SSD.
     */
    'temp_dir' => env('EXPORT_TEMP_DIR', sys_get_temp_dir()),
];
