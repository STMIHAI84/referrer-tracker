<?php

namespace App\Services;

use App\Models\Referral;
use App\Support\SourceDetector;
use Illuminate\Http\Request;

class ReferralService
{
    /** Schimbă în true dacă vrei să salvezi și boții */
    private const STORE_BOTS = true;

    /** Surse permise (normalizate) */
    private const ALLOWED_SOURCES = [
        'facebook','instagram','twitter','linkedin','whatsapp','telegram','google',
        'facebook-app','direct','other',
        // boți (dacă STORE_BOTS = true)
        'bot:facebook','bot:telegram','bot:google','bot:bing','bot:twitter','bot:linkedin','bot:slack','bot:discord','bot:other',
    ];

    private const SOURCE_ALIASES = [
        'fb' => 'facebook', 'facebook-app' => 'facebook', 'x' => 'twitter',
        'ig' => 'instagram', 'wa' => 'whatsapp', 't.me' => 'telegram',
        'wa.me' => 'whatsapp', 'lnkd' => 'linkedin',
    ];

    private function normalizeSource(string $src): string
    {
        $src = strtolower($src);
        return self::SOURCE_ALIASES[$src] ?? $src;
    }

    private function guessSourceFromHost(?string $host): ?string
    {
        if (!$host) return null;
        $h = strtolower($host);

        $map = [
            'facebook' => ['facebook.com','m.facebook.com','l.facebook.com','fb.com'],
            'instagram'=> ['instagram.com'],
            'twitter'  => ['twitter.com','x.com','t.co'],
            'linkedin' => ['linkedin.com','lnkd.in'],
            'telegram' => ['t.me','telegram.org'],
            'whatsapp' => ['wa.me','whatsapp.com'],
            'google'   => ['google.','g.page','goo.gl'],
        ];

        foreach ($map as $source => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($h, $needle)) return $source;
            }
        }
        return 'other';
    }

    private function shouldStoreBasedOnBot(string $source): bool
    {
        $isBot = str_starts_with($source, 'bot:');
        return $isBot ? self::STORE_BOTS : true;
    }

    private function isInternalTraffic(?string $refHost, bool $hasExplicitSource): bool
    {
        if ($hasExplicitSource) return false; // UTM/ref/source în URL bat refererul intern
        if (!$refHost) return false;
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $appHost = $appHost ? preg_replace('/^www\./', '', $appHost) : null;
        $refHost = preg_replace('/^www\./', '', $refHost);
        return $appHost && $refHost && ($appHost === $refHost);
    }

    public function trackReferral(Request $request): ?Referral
    {
        $det    = SourceDetector::detect($request);
        $source = strtolower($det['source'] ?? 'direct');

        $utmSource = $request->input('utm_source');
        $refParam  = $request->input('ref');
        $srcParam  = $request->input('source');

        if (!$utmSource) {
            $utmSource = $this->guessSourceFromHost($det['host_referrer'] ?? null);
        }

        foreach ([$utmSource, $refParam, $srcParam] as $cand) {
            if ($cand) {
                $source = $this->normalizeSource((string)$cand);
                break;
            }
        }

        $hasExplicitSource = (bool) ($utmSource || $refParam || $srcParam);
        if (!$hasExplicitSource && $this->isInternalTraffic($det['host_referrer'] ?? null, $hasExplicitSource)) {
            return null;
        }

        if (!$this->shouldStoreBasedOnBot($source)) {
            return null;
        }

        // validare în lista permisă
        if (!in_array($source, self::ALLOWED_SOURCES, true)) {
            $source = 'other';
        }

        // Aici ar trebui să salvezi referral-ul în baza de date
        // Exemplu:
        // return Referral::create(['source' => $source, ...]);

        return null; // Temporar - trebuie implementat salvare
    }

    public function getTrackingMessage(?Referral $referral): string
    {
        return $referral
            ? "Sursa înregistrată: {$referral->source}"
            : "Nu am primit sursă externă (trafic direct, intern sau blocat).";
    }

    public function generateTrackingLink(string $baseUrl, array $params): string
    {
        return $baseUrl.(str_contains($baseUrl,'?') ? '&' : '?').http_build_query($params);
    }
}
