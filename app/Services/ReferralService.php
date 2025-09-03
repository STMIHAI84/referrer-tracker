<?php

namespace App\Services;

use App\Models\Referral;
use App\Support\SourceDetector;
use Illuminate\Http\Request;

class ReferralService
{
    private const STORE_BOTS = true;

    private const ALLOWED_SOURCES = [
        'facebook','instagram','twitter','linkedin','whatsapp','telegram','google',
        'facebook-app','direct','other',
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

    public function trackReferral(Request $request): ?Referral
    {

        $det    = SourceDetector::detect($request);
        $source = strtolower($det['source'] ?? 'direct');

        // 1) Surse explicite din URL au prioritate
        $utmSource = $request->input('utm_source');
        $refParam  = $request->input('ref');
        $srcParam  = $request->input('source');


        if (!$utmSource) {
            $utmSource = $this->guessSourceFromHost($det['host_referrer'] ?? null);
        }

        foreach ([$utmSource, $refParam, $srcParam] as $cand) {
            if ($cand) { $source = $this->normalizeSource((string)$cand); break; }
        }

        $hasExplicitSource = (bool) ($utmSource || $refParam || $srcParam);

        if (!$hasExplicitSource && $this->isInternalTraffic($det['host_referrer'] ?? null, $hasExplicitSource)) {
            return null;
        }

        if (!$this->shouldStoreBasedOnBot($source)) {
            return null;
        }

        if (!in_array($source, self::ALLOWED_SOURCES, true)) {
            $source = 'other';
        }

        return Referral::create([
            'referrer_url'   => $det['referer_raw'] ?? null,
            'referrer_host'  => $det['host_referrer'] ?? null,
            'source'         => $source,
            'utm_source'     => $utmSource ?: null,
            'utm_medium'     => $request->input('utm_medium'),
            'utm_campaign'   => $request->input('utm_campaign'),
            'utm_term'       => $request->input('utm_term'),
            'utm_content'    => $request->input('utm_content'),
            'gclid'          => $request->input('gclid'),
            'fbclid'         => $request->input('fbclid'),
            'ttclid'         => $request->input('ttclid'),
            'referral_code'  => $request->input('ref'),
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

    private function isInternalTraffic(?string $refHost, bool $hasExplicitSource): bool
    {
        if ($hasExplicitSource) return false;
        if (!$refHost) return false;

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $appHost = $appHost ? preg_replace('/^www\./', '', $appHost) : null;
        $refHost = preg_replace('/^www\./', '', $refHost);

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
