
# Instalează și rulează
# Instalează dependințele
composer install

# Generează cheia aplicației
php artisan key:generate

# Rulează migrările bazei de date
php artisan migrate

# Pornește serverul
php artisan serve

Testare Imediată 
URL-uri importante:
text
► Pagina Principală:   http://localhost:8000/landing
► Admin Panel:         http://localhost:8000/admin/referrers
► Pagini Test:         http://localhost:8000/test-page-1
http://localhost:8000/test-page-2

Testează funcționalitatea:

# Test trafic de pe Facebook (se înregistrează)
curl -H "Referer: https://facebook.com" http://localhost:8000/landing

# Test trafic de pe WhatsApp (se înregistrează)
curl -H "Referer: https://web.whatsapp.com" http://localhost:8000/landing

# Test trafic intern (nu se înregistrează)
curl -H "Referer: http://localhost:8000/test-page-1" http://localhost:8000/landing

Verifică rezultatele:
Accesează http://localhost:8000/admin/referrers pentru a vedea toate referrer-ele înregistrate.
