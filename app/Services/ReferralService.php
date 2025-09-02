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

    public function trackReferral(Request $request): ?Referral
    {
        // 1) Determină sursa
        $det = SourceDetector::detect($request); // source, referer_raw, user_agent, host_referrer
        $source = strtolower($det['source'] ?? 'direct');

        // 2) Exclude trafic intern (același host ca APP_URL)
        if ($this->isInternalTraffic($det['host_referrer'])) {
            return null;
        }

        // 3) Optional: nu salva boții
        if (!$this->shouldStoreBasedOnBot($source)) {
            return null;
        }

        // 4) Optional: dacă permiți UTM/ref param ca OVERRIDE (doar dacă există)
        $utmSource = $request->input('utm_source');
        $refParam  = $request->input('ref');
        $srcParam  = $request->input('source');
        if ($utmSource)       $source = strtolower($utmSource);
        elseif ($refParam)    $source = strtolower($refParam);
        elseif ($srcParam)    $source = strtolower($srcParam);

        // 5) Surse permise
        if (!in_array($source, self::ALLOWED_SOURCES, true)) {
            $source = 'other';
        }

        // 6) Creează înregistrarea
        return Referral::create([
            'referrer_url'   => $det['referer_raw'] ?? null,
            'referrer_host'  => $det['host_referrer'] ?? null,
            'source'         => $source,
            'utm_source'     => $utmSource,
            'utm_medium'     => $request->input('utm_medium'),
            'utm_campaign'   => $request->input('utm_campaign'),
            'referral_code'  => $request->input('ref'),
            'landing_path'   => '/'.$request->path(),
            'ip'             => $request->ip(),
            'user_agent'     => $det['user_agent'] ?? $request->userAgent(),
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
