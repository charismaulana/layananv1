# Ramba Meal Planning

Aplikasi perencanaan makan berbasis web yang dibangun dengan Laravel 12.

---

## 🚀 Deploy ke Shared Hosting (cPanel)

### Struktur Folder di Hosting

```
/home/username/
├── public_html/           ← Folder yang diakses publik (domain)
│   ├── index.php          ← Disalin dari public/index.php
│   ├── .htaccess          ← Disalin dari public/.htaccess
│   ├── favicon.ico        ← Disalin dari public/favicon.ico
│   ├── robots.txt         ← Disalin dari public/robots.txt
│   └── build/             ← Disalin dari public/build/
│
└── ramba-meal/            ← Project Laravel (hasil git pull)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── ...
```

### Langkah 1 — Clone/Pull Project Laravel

Masuk ke folder **di luar public_html** melalui Terminal cPanel:

```bash
cd /home/username/
git clone https://github.com/username/ramba-meal.git
# atau jika sudah ada:
cd ramba-meal && git pull origin main
```

### Langkah 2 — Install Dependencies

```bash
cd /home/username/ramba-meal
composer install --no-dev --optimize-autoloader
```

### Langkah 3 — Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database hosting:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainanda.com

DB_HOST=localhost
DB_DATABASE=nama_database_cpanel
DB_USERNAME=user_database_cpanel
DB_PASSWORD=password_database_cpanel
```

### Langkah 4 — Jalankan Migrasi & Cache

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### Langkah 5 — Salin File `public/` ke `public_html/`

Salin semua isi folder `ramba-meal/public/` ke `public_html/`:

```bash
cp -r /home/username/ramba-meal/public/. /home/username/public_html/
```

> **Catatan**: File `public/index.php` sudah dikonfigurasi untuk menunjuk ke folder `ramba-meal/` secara otomatis.

### Langkah 6 — Set Permission Storage

```bash
chmod -R 775 /home/username/ramba-meal/storage
chmod -R 775 /home/username/ramba-meal/bootstrap/cache
```

---

## 💻 Menjalankan Secara Lokal (Development)

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL

### Instalasi

```bash
# Clone project
git clone https://github.com/username/ramba-meal.git
cd ramba-meal

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Build assets
npm run build

# Jalankan server
php artisan serve
```

Akses di: `http://localhost:8000`

---

## 🔄 Update Project di Hosting

Setiap kali ada update dari GitHub:

```bash
cd /home/username/ramba-meal
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Salin ulang file public jika ada perubahan
cp -r /home/username/ramba-meal/public/. /home/username/public_html/
```

---

## 📋 Teknologi

- **Framework**: Laravel 12
- **PHP**: >= 8.2
- **Database**: MySQL
- **CSS**: Tailwind CSS
- **Build Tool**: Vite
- **Export**: Maatwebsite Excel, Laravel DomPDF
- **QR Code**: SimpleSoftwareIO QrCode

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
