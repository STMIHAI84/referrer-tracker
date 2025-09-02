<?php

namespace App\Services;

use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralService
{
    public function trackReferral(Request $request): ?Referral
    {
        $source = $this->determineSource($request);

        // Verifică dacă trebuie să track-uiască (exclude sursele interne)
        if (!$this->shouldTrackSource($source, $request)) {
            return null;
        }

        // Creează înregistrarea
        return Referral::create([
            'referrer_url' => $this->getReferrerUrl($request),
            'referrer_host' => $this->getReferrerUrl($request) ? parse_url($this->getReferrerUrl($request), PHP_URL_HOST) : null,
            'source' => $source,
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
            'referral_code' => $request->input('ref'),
            'landing_path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'full_url' => $request->fullUrl(),
        ]);
    }

    protected function determineSource(Request $request): string
    {
        // Prioritizează parametrii URL peste HTTP Referer
        if ($utmSource = $request->input('utm_source')) {
            return strtolower($utmSource);
        }

        if ($ref = $request->input('ref')) {
            return strtolower($ref);
        }

        if ($source = $request->input('source')) {
            return strtolower($source);
        }

        // Fallback la HTTP Referer (dacă este disponibil)
        $referrerUrl = $this->getReferrerUrl($request);
        if ($referrerUrl) {
            return $this->parseSourceFromUrl($referrerUrl);
        }

        return 'direct';
    }

    protected function parseSourceFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        $domainSources = [
            'facebook.com' => 'facebook',
            'instagram.com' => 'instagram',
            'twitter.com' => 'twitter',
            'linkedin.com' => 'linkedin',
            'whatsapp.com' => 'whatsapp',
            'google.com' => 'google',
        ];

        foreach ($domainSources as $domain => $source) {
            if (str_contains($host, $domain)) {
                return $source;
            }
        }

        return 'other';
    }

    protected function shouldTrackSource(string $source, Request $request): bool
    {
        // Verifică dacă este trafic intern (același domeniu)
        if ($this->isInternalTraffic($request)) {
            return false;
        }

        // Listă de surse permise
        $allowedSources = ['facebook', 'instagram', 'whatsapp', 'twitter', 'linkedin', 'google', 'organic', 'direct', 'other'];

        return in_array($source, $allowedSources);
    }

    protected function isInternalTraffic(Request $request): bool
    {
        $referrerUrl = $this->getReferrerUrl($request);
        if (!$referrerUrl) {
            return false;
        }

        $referrerHost = parse_url($referrerUrl, PHP_URL_HOST);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        $localHosts = ['localhost', '127.0.0.1', '::1'];

        if (in_array($referrerHost, $localHosts) && in_array($appHost, $localHosts)) {
            return true;
        }

        return $referrerHost === $appHost;
    }

    protected function getReferrerUrl(Request $request): ?string
    {
        return $request->headers->get('referer');
    }

    public function getTrackingMessage(?Referral $referral): string
    {
        if ($referral) {
            return "Sursa înregistrată: {$referral->source}";
        }

        return "Nu am primit sursă externă (trafic direct, intern sau blocat).";
    }

    // Funcție utilitară pentru generat link-uri
    public function generateTrackingLink(string $baseUrl, array $params): string
    {
        return $baseUrl . '?' . http_build_query($params);
    }
}
