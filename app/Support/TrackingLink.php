<?php

namespace App\Support;

use Illuminate\Support\Str;

class TrackingLink
{
    /** Surse permise (normalizează tot la lowercase) */
    private const ALLOWED_SOURCES = [
        'telegram','whatsapp','instagram','facebook','facebook-app',
        'linkedin','twitter','google','email','direct','other',
    ];

    /**
     * Generează link de tracking.
     * - $to: URL final (spre care se face redirect/pe care îl etichetăm direct)
     * - $params: sd/utm + opțional _sdclid
     * - $viaRedirect: true => /r?…&sig=… ; false => link direct etichetat
     */
    public static function make(string $to, array $params = [], bool $viaRedirect = true): string
    {
        // 1) normalizează și pregătește payloadul
        $clid     = $params['_sdclid'] ?? (string) Str::uuid();
        $sdSource = self::normalizeSource($params['sd_source'] ?? ($params['utm_source'] ?? 'direct'));

        // validează sursa (fallback -> other)
        if (!in_array($sdSource, self::ALLOWED_SOURCES, true)) {
            $sdSource = 'other';
        }

        $payload = array_filter([
            'to'           => $to,
            '_sdclid'      => $clid,
            'sd_source'    => $sdSource,
            'utm_source'   => $params['utm_source'] ?? null,
            'utm_medium'   => $params['utm_medium'] ?? null,
            'utm_campaign' => $params['utm_campaign'] ?? null,
            'utm_term'     => $params['utm_term'] ?? null,
            'utm_content'  => $params['utm_content'] ?? null,
        ], fn($v) => $v !== null && $v !== '');

        // 2) Mod „via redirector” (/r?…&sig=…)
        if ($viaRedirect) {
            $base = route('track.redirect'); // asigură-te că ruta există
            $sig  = hash_hmac('sha256', self::canonical($payload), config('app.key'));

            // IMPORTANT: nu dubla 'to' (e deja în $payload)
            $qs = http_build_query($payload + ['sig' => $sig], '', '&', PHP_QUERY_RFC3986);
            return $base . '?' . $qs;
        }

        // 3) Mod „link direct” (fără semnătură, doar parametrii în URL-ul final)
        $glue = str_contains($to, '?') ? '&' : '?';
        $qs   = http_build_query([
            '_sdclid'      => $clid,
            'sd_source'    => $sdSource,
            'utm_source'   => $payload['utm_source'] ?? null,
            'utm_medium'   => $payload['utm_medium'] ?? null,
            'utm_campaign' => $payload['utm_campaign'] ?? null,
            'utm_term'     => $payload['utm_term'] ?? null,
            'utm_content'  => $payload['utm_content'] ?? null,
        ], '', '&', PHP_QUERY_RFC3986);

        // curăță dubluri „name=” goale
        $qs = preg_replace('/(&?[^=&]+=&)/', '&', $qs);
        $qs = rtrim($qs, '&');

        return $to . ($qs ? $glue . $qs : '');
    }

    private static function canonical(array $data): string
    {
        ksort($data);
        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }

    private static function normalizeSource(?string $src): string
    {
        return strtolower(trim((string) $src));
    }
}
