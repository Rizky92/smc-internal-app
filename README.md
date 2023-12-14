## System requirement
1. PHP 7.4
1. Composer 2+
1. MariaDB 10.4/MySQL 5.7

## Quick setup
### 1. Install projek
via SSH
```sh
git clone git@github.com:rizky92/smc-internal-app.git "smc-internal-app"
```

via HTTPS
```sh
git clone https://github.com/rizky92/smc-internal-app.git "smc-internal-app"
```

Kemudian masuk ke directory dan install dependency.
```sh
cd "smc-internal-app"
composer install
```

### 2. Setup environment
Copy file .env.example ke .env

Linux/Mac OS
```sh
cp .env.example .env
```

Windows (CMD)
```bat
copy .env.example .env
```

Windows (Powershell)
```powershell
Copy-Item .env.example -Destination .env
```

Kemudian konfigurasi isi .env sesuai dengan kebutuhan

### 3. Migrasi database
Jalankan perintah berikut untuk melakukan migrasi database, pastikan nama database sudah benar dan sesuai.
```sh
php artisan migrate --seed
```