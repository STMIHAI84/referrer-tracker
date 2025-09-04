<?php

namespace App\Services;

use App\Models\Referral;
use App\Support\SourceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralService
{
    /** Schimbă în true dacă vrei să salvezi și boții */
    private const STORE_BOTS = true;

    /** Surse permise (normalizate) */
    private const ALLOWED_SOURCES = [
        'facebook','instagram','twitter','linkedin','whatsapp','telegram','google',
        'facebook-app','email','direct','other',
        // boți
        'bot:facebook','bot:telegram','bot:google','bot:bing','bot:twitter','bot:linkedin','bot:slack','bot:discord','bot:other',
    ];

    /** Aliasuri uzuale */
    private const SOURCE_ALIASES = [
        'fb' => 'facebook', 'facebook-app' => 'facebook-app', 'x' => 'twitter',
        'ig' => 'instagram', 'wa' => 'whatsapp', 't.me' => 'telegram',
        'wa.me' => 'whatsapp', 'lnkd' => 'linkedin',
    ];

    private function normalizeSource(?string $src): ?string
    {
        if (!$src) return null;
        $src = strtolower(trim($src));
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

    public function trackReferral(Request $request): ?Referral
    {
        // Identificatori / parametri expliciți (au prioritate)
        $clid      = $request->cookie('_sdclid') ?: $request->query('_sdclid') ?: (string) Str::uuid();
        $sdSource  = $this->normalizeSource($request->cookie('sd_source') ?: $request->query('sd_source'));
        $utmSource = $this->normalizeSource($request->query('utm_source'));
        $refParam  = $this->normalizeSource($request->query('ref'));
        $srcParam  = $this->normalizeSource($request->query('source'));

        // Referer & UA (fallback)
        $referrerUrl  = $request->headers->get('referer'); // (sic) "referer"
        $referrerHost = $referrerUrl ? parse_url($referrerUrl, PHP_URL_HOST) : null;
        $ua           = strtolower($request->userAgent() ?? '');

        // Detector intern (NU trebuie să suprascrie sursele explicite)
        $det = SourceDetector::detect($request); // ['source','referer_raw','user_agent','host_referrer']
        $detSource = $this->normalizeSource($det['source'] ?? null);
        $hostSource = $this->guessSourceFromHost($det['host_referrer'] ?? $referrerHost);

        // 1) Prioritate: sd_source / utm_source / ref|source (query)
        $source = $sdSource
            ?? $utmSource
            ?? $refParam
            ?? $srcParam
            // 2) Apoi: SourceDetector raportat, apoi host
            ?? $detSource
            ?? $hostSource
            // 3) UA ca ultim fallback
            ?? ($ua && str_contains($ua,'instagram') ? 'instagram'
                : ($ua && (str_contains($ua,'fban') || str_contains($ua,'fbav')) ? 'facebook-app'
                    : ($ua && str_contains($ua,'telegram') ? 'telegram'
                        : ($ua && str_contains($ua,'whatsapp') ? 'whatsapp'
                            : null))));

        // 4) Tot n-ai nimic? direct
        $source = $this->normalizeSource($source ?? 'direct');

        // Filtru trafic intern DOAR dacă nu avem sursă explicită (sd/utm/ref/source)
        $hasExplicit = (bool) ($sdSource || $utmSource || $refParam || $srcParam);
        if (!$hasExplicit && $this->isInternalTraffic($det['host_referrer'] ?? $referrerHost)) {
            return null;
        }

        // Boți?
        if (!$this->shouldStoreBasedOnBot($source)) return null;

        // Normalizează în setul permis (altfel other)
        if (!in_array($source, self::ALLOWED_SOURCES, true)) $source = 'other';

        return Referral::create([
            'clid'           => $clid,
            'source'         => $source,
            'sd_source'      => $sdSource,
            'utm_source'     => $utmSource,
            'utm_medium'     => $request->input('utm_medium'),
            'utm_campaign'   => $request->input('utm_campaign'),
            'utm_term'       => $request->input('utm_term'),
            'utm_content'    => $request->input('utm_content'),
            'gclid'          => $request->input('gclid'),
            'fbclid'         => $request->input('fbclid'),
            'ttclid'         => $request->input('ttclid'),
            'referral_code'  => $request->input('ref'),
            'referrer_url'   => $det['referer_raw'] ?? $referrerUrl,
            'referrer_host'  => $det['host_referrer'] ?? $referrerHost,
            'landing_path'   => '/'.ltrim($request->path(), '/'),
            'ip'             => $request->ip(),
            'user_agent'     => ($det['user_agent'] ?? $request->userAgent()),
            'full_url'       => $request->fullUrl(),
        ]);
    }

    private function shouldStoreBasedOnBot(string $source): bool
    {
        $isBot = str_starts_with($source, 'bot:');
        return $isBot ? self::STORE_BOTS : true;
    }

    private function isInternalTraffic(?string $refHost): bool
    {
        if (!$refHost) return false;
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $appHost = $appHost ? preg_replace('/^www\./', '', strtolower($appHost)) : null;
        $refHost = preg_replace('/^www\./', '', strtolower($refHost));

        // relax în local
        $local = ['localhost','127.0.0.1','::1'];
        if (in_array($appHost, $local, true) && in_array($refHost, $local, true)) return true;

        return $appHost && $refHost && ($appHost === $refHost);
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
