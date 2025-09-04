<?php
namespace App\Support;

use Illuminate\Support\Str;

class TrackingLink
{
/** Sursa permisă: ajustează după ce vrei să măsori */
private const ALLOWED_SOURCES = [
'telegram','whatsapp','instagram','facebook','facebook-app',
'linkedin','twitter','google','email','direct','other'
];

public static function make(string $to, array $params = [], bool $viaRedirect = true): string
{
// click-id (first-party)
$clid = $params['_sdclid'] ?? (string) Str::uuid();

// sursa explicită (obligatoriu pentru acuratețe)
$sd = strtolower($params['sd_source'] ?? $params['utm_source'] ?? 'direct');
if (!in_array($sd, self::ALLOWED_SOURCES, true)) $sd = 'other';

// utm-uri (opționale)
$utm = [
'utm_source'   => $params['utm_source']   ?? $sd,
'utm_medium'   => $params['utm_medium']   ?? null,
'utm_campaign' => $params['utm_campaign'] ?? null,
'utm_term'     => $params['utm_term']     ?? null,
'utm_content'  => $params['utm_content']  ?? null,
];

// payload comun
$payload = array_filter(array_merge([
'_sdclid'   => $clid,
'sd_source' => $sd,
], $utm), fn($v) => $v !== null && $v !== '');

if (!$viaRedirect) {
// link direct pe /landing
$qs = http_build_query($payload);
return rtrim($to, '?').(str_contains($to,'?') ? '&' : '?').$qs;
}

// link prin redirector /r (include semnătură HMAC)
$toEnc = urlencode($to);
$base  = url('/r');
$data  = array_merge(['to' => $to], $payload);

$sig   = hash_hmac('sha256', self::canonical($data), config('app.key'));

$qs = http_build_query(array_merge(['to' => $to], $payload, ['sig' => $sig]));
return $base.'?'.$qs;
}

private static function canonical(array $data): string
{
ksort($data);
return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
}
}
