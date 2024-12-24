# SMC Internal App (SIAP)
## System Requirement
1. PHP ^7.4 dan MariaDB ^10.4, bisa menggunakan [XAMPP](https://www.apachefriends.org/download.html) atau [Laragon](https://laragon.org/download/)
2. [Composer ^2.2](https://getcomposer.org/download/)
3. Ekstensi [PHP `xlswriter` ^1.5](https://github.com/viest/php-ext-xlswriter), 

## Instalasi
Git clone repo dan masuk ke directory.
```bash
git clone https://github.com/rizky92/smc-internal-app.git --depth=1 --branch=live smc-internal-app \
    && cd smc-internal-app
```

Kemudian install dependency menggunakan composer, lalu copy file `.env.example` menjadi `.env` di directory yang sama.
```bash
composer install && cp .env.example .env
```

Lakukan konfigurasi koneksi database, nama superadmin, userkey dan passkey login di file .env yang baru dicopy tadi pada bagian `# WAJIB`.

Buka file `database/seeders/PermissionSeeder.php` kemudian cari `User::findByNRP('221203')`, ganti dengan ID user yang akan ditunjuk menjadi role superadmin.

Apabila sudah, jalankan perintah berikut secara berurutan.
```bash
php artisan key:generate \
    && php artisan impersonate-key:generate \
    && php artisan migrate --seed
```

Test aplikasi berhasil diinstal dengan perintah berikut, lalu buka http://127.0.0.1:8000.
```bash
php artisan serve
```

Login menggunakan akun user dari SIMRS Khanza.