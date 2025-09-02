# Imagine PHP CLI (fără FPM), suficientă pentru php -S (dev friendly)
FROM php:8.2-cli

# Extensii necesare (pdo_pgsql pentru Postgres de pe Render)
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql bcmath \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Director lucru
WORKDIR /app

# Copiem codul (păstrăm cache Composer pe cât posibil)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader || true

# Copiem restul aplicației
COPY . .

# Re-run composer ca să prindă noile fișiere (dacă lock nu exista)
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Optimizări Laravel (nu folosim config:cache înainte de APP_KEY/ENV valide,
# le rulăm la start)
# Expunem portul (Render setează $PORT oricum)
EXPOSE 10000

# Comanda de start:
# 1) Dacă APP_KEY lipsește, oprim cu mesaj clar
# 2) rulăm migrările (DB e disponibilă la runtime pe Render)
# 3) cache config/rute/view
# 4) pornim serverul built-in pe $PORT
CMD bash -lc '\
  if [ -z "$APP_KEY" ]; then echo "ERROR: APP_KEY nu este setat! Adaugă APP_KEY în Environment pe Render."; exit 1; fi && \
  php artisan migrate --force && \
  php artisan config:cache && php artisan route:cache && php artisan view:cache && \
  php -S 0.0.0.0:${PORT:-10000} -t public \
'
