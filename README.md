# Referral Tracker – Laravel

Aplicație Laravel pentru tracking referrers, UTM-uri și surse de trafic (Facebook, Instagram, WhatsApp, Telegram etc.) cu interfață de administrare și export CSV.

## Instalare locală

1. Instalează dependențele
composer install

2. Generează cheia aplicației
php artisan key:generate

3. Rulează migrațiile bazei de date
php artisan migrate

4. Pornește serverul de dezvoltare
php artisan serve

Aplicația va rula implicit pe http://localhost:8000

## Rute principale

- Landing Page: http://localhost:8000/landing
- Generator Link-uri: http://localhost:8000/generate-links
- Admin Panel: http://localhost:8000/admin/referrers
- Export CSV: http://localhost:8000/admin/referrers/export
- Pagini Test:
  - http://localhost:8000/test-page-1
  - http://localhost:8000/test-page-2

## Testare rapidă

Trafic extern:

curl -H "Referer: https://facebook.com" http://localhost:8000/landing
curl -H "Referer: https://web.whatsapp.com" http://localhost:8000/landing

Trafic intern (nu se înregistrează):

curl -H "Referer: http://localhost:8000/test-page-1" http://localhost:8000/landing

## Funcționalități

- Detectare automată sursă pe baza Referer și UTM-uri
- Salvare IP, User-Agent, full URL, landing page
- Interfață Admin cu:
  - filtre avansate (source, UTM, interval date, text search, excludere boți)
  - statistici rapide (trafic direct, IP-uri unice, surse unice)
  - export CSV streaming (scalabil, compatibil Excel)
- Pagini de test pentru verificare tracking intern
- Generator link-uri pentru crearea rapidă de URL-uri de tracking

## Tehnologii folosite

- Laravel 11
- PostgreSQL (Render free DB)
- Blade pentru interfață
- CSS custom și TailwindCSS
- Docker + Render Deploy
