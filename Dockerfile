FROM php:8.2-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql bcmath \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Instalează dependențele pe baza lock-ului (dacă există)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader || true

# Copiază restul codului
COPY . .
RUN composer install --no-dev --prefer-dist --optimize-autoloader

EXPOSE 10000

CMD bash -lc '\
  if [ -z "$APP_KEY" ]; then echo "ERROR: APP_KEY nu este setat!"; exit 1; fi; \
  echo "DATABASE_URL=${DATABASE_URL}"; \
  # 1) Pornește serverul web imediat (ca health-check-ul să aibă ce verifica)
  php -S 0.0.0.0:${PORT:-10000} -t public & \
  WEB_PID=$!; \
  # 2) Încearcă migrările cu retry 15x, DAR fără să oprești serverul dacă eșuează
  for i in {1..15}; do \
    php artisan migrate --force && break || { echo "Migrate try $i failed; retrying..."; sleep 3; }; \
  done; \
  # 3) Cache după ce env-urile sunt vizibile
  php artisan config:cache || true; \
  php artisan route:cache || true; \
  php artisan view:cache || true; \
  # 4) Ține procesul principal viu
  wait $WEB_PID \
'

