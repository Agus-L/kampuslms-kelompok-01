FROM php:8.4-apache

# 1. Install ekstensi sistem, library PHP, pustaka font/gambar untuk PDF (Dompdf), dan SQLite
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    curl \
    supervisor \
    && mkdir -p /var/log/supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install Node.js 20 & NPM (Wajib untuk kompilasi Vite & Tailwind CSS v4)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3. Konfigurasi Apache: aktifkan mod_rewrite, pastikan hanya mpm_prefork aktif, dan AllowOverride All
RUN a2enmod rewrite \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 4. Atur Document Root Apache ke /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Salin Composer resmi dari image composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Salin seluruh kode proyek ke container
WORKDIR /var/www/html
COPY . .

# 7. Install dependensi backend PHP (Composer) untuk mode produksi
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 8. Kompilasi aset frontend (Vite & Tailwind CSS v4) lalu bersihkan node_modules agar image ramping
RUN npm ci && npm run build && rm -rf node_modules

# 9. Siapkan file database SQLite dan berikan hak akses storage & cache
RUN mkdir -p database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# 10. Salin konfigurasi Supervisor untuk mengelola Apache dan Queue Worker
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY supervisord.conf /etc/supervisor/supervisord.conf

EXPOSE 80 8080

# 11. Script startup: sesuaikan port dinamis Railway, buat storage:link, jalankan migrasi & seed, lalu jalankan Supervisor
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* && export PORT=\"${PORT:-8080}\" && sed -i \"s/Listen .*/Listen $PORT/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost \\*:$PORT>/\" /etc/apache2/sites-available/000-default.conf && php artisan storage:link --force && php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan migrate --force || true) && (php artisan db:seed --force || true) && exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf"]
