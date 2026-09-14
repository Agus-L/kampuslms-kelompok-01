# Deployment Guide — KampusLMS

## Akun Demo untuk Penguji

Akun-akun berikut tersedia di semua environment (dev, staging, produksi):

| Role      | Email                        | Password (dev) | Password (produksi) |
|-----------|------------------------------|----------------|---------------------|
| Admin     | `admin@kampuslms.test`       | `password`     | `KampusLMS@Admin2026!`  |
| Dosen     | `dosen@kampuslms.test`       | `password`     | `KampusLMS@Dosen2026!`  |
| Mahasiswa | `mahasiswa@kampuslms.test`   | `password`     | `KampusLMS@Mhs2026!`    |

> **⚠️ PENTING:** Password produksi di atas WAJIB diganti sebelum deployment.
> Gunakan environment variable `DEMO_PASSWORD` atau jalankan DemoAccountSeeder
> dengan password yang sudah diubah.

---

## Langkah Deployment

### 1. Setup Environment

```bash
cp .env.example .env
# Edit .env: sesuaikan DB_*, APP_URL, APP_ENV=production, APP_DEBUG=false
```

### 2. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
npm ci && npm run build
```

### 3. Generate Key & Migrate

```bash
php artisan key:generate
php artisan migrate --force
```

### 4. Seed Database

```bash
# Seed seluruh data (termasuk akun demo dengan password 'password')
php artisan db:seed --force

# WAJIB: Ganti password akun demo untuk produksi
# Opsi A: Set environment variable lalu jalankan seeder
export DEMO_PASSWORD="KampusLMS@Admin2026!"
php artisan db:seed --class=DemoAccountSeeder --force

# Opsi B: Edit langsung di DemoAccountSeeder.php sebelum deploy
```

### 5. Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Mengganti Password Akun Demo di Produksi

DemoAccountSeeder mendukung environment variable `DEMO_PASSWORD`:

```bash
# Di file .env produksi
DEMO_PASSWORD="GantiDenganPasswordKuat!"

# Kemudian jalankan
php artisan db:seed --class=DemoAccountSeeder --force
```

Atau langsung via tinker:

```bash
php artisan tinker
>>> \App\Models\User::where('email', 'admin@kampuslms.test')->update(['password' => bcrypt('PasswordBaru!')]);
>>> \App\Models\User::where('email', 'dosen@kampuslms.test')->update(['password' => bcrypt('PasswordBaru!')]);
>>> \App\Models\User::where('email', 'mahasiswa@kampuslms.test')->update(['password' => bcrypt('PasswordBaru!')]);
```

---

## Reset Data (Development)

```bash
php artisan migrate:fresh --seed
```

Seeder bersifat **idempoten** — aman dijalankan ulang kapan saja.
