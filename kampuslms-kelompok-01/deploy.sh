#!/usr/bin/env bash
set -e

echo "Mengaktifkan maintenance mode..."
php artisan down || true

echo "Menarik kode terbaru dari Git..."
git pull origin main

echo "Memperbarui dependensi Composer..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "Mengompilasi aset frontend (Vite & Tailwind CSS)..."
npm ci --no-audit
npm run build

echo "Menjalankan migrasi database..."
php artisan migrate --force

echo "Memastikan symbolic link storage terpasang..."
php artisan storage:link || true

echo "Mengoptimalkan konfigurasi dan route cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Merestart queue worker..."
php artisan queue:restart

echo "Menonaktifkan maintenance mode..."
php artisan up

echo "Deployment selesai dengan sukses."
