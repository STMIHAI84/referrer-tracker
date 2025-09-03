<?php

namespace App\Support;

use Illuminate\Http\Request;

final class SourceDetector
{
    /** Domenii cunoscute -> etichetă sursă */
    private const HOST_MAP = [
        // Facebook & Instagram
        'facebook.com'      => 'facebook',
        'm.facebook.com'    => 'facebook',
        'l.facebook.com'    => 'facebook',
        'lm.facebook.com'   => 'facebook',
        'instagram.com'     => 'instagram',
        // X / Twitter
        't.co'              => 'twitter',
        'x.com'             => 'twitter',
        'twitter.com'       => 'twitter',
        // Google
        'google.com'        => 'google',
        'www.google.com'    => 'google',
        // WhatsApp / Telegram
        'wa.me'             => 'whatsapp',
        'web.whatsapp.com'  => 'whatsapp',
        't.me'              => 'telegram',
        // Tester tools
        'cdpn.io'           => 'codepen',
        'fiddle.jshell.net' => 'jsfiddle',
        'github.io'         => 'github-pages',
        'githubusercontent.com' => 'gist',
    ];

    /** Regex UA boți frecvenți */
    private const BOT_REGEX = '/facebookexternalhit|TelegramBot|Googlebot|bingbot|Discordbot|Twitterbot|LinkedInBot|Slackbot/i';

    /** Normalizează un host (fără www.) */
    public static function normalizeHost(?string $host): ?string
    {
        if (!$host) return null;
        $host = strtolower($host);
        return preg_replace('/^www\./', '', $host);
    }

    /** Mapează un host la o sursă prietenoasă (sau îl întoarce ca atare) */
    public static function mapHostToSource(?string $host): ?string
    {
        $host = self::normalizeHost($host);
        if (!$host) return null;

        // hit direct mapping
        if (isset(self::HOST_MAP[$host])) {
            return self::HOST_MAP[$host];
        }

        foreach (array_keys(self::HOST_MAP) as $needle) {
            if (str_contains($host, $needle)) {
                return self::HOST_MAP[$needle];
            }
        }

        return $host;
    }

    /** Returnează true dacă UA pare să fie bot */
    public static function isBot(?string $ua): bool
    {
        if (!$ua) return false;
        return (bool) preg_match(self::BOT_REGEX, $ua);
    }

    /** Determină sursa + câmpuri utile pentru salvare */
    public static function detect(Request $request): array
    {
        $ua      = $request->userAgent() ?? '';
        $referer = $request->headers->get('referer');
        $host    = $referer ? parse_url($referer, PHP_URL_HOST) : null;
        $host    = self::normalizeHost($host);

        if (self::isBot($ua)) {
            $label = 'bot:other';
            if (stripos($ua, 'facebook') !== false) $label = 'bot:facebook';
            elseif (stripos($ua, 'telegram') !== false) $label = 'bot:telegram';
            elseif (stripos($ua, 'google') !== false) $label = 'bot:google';
            elseif (stripos($ua, 'bing') !== false) $label = 'bot:bing';
            elseif (stripos($ua, 'twitter') !== false) $label = 'bot:twitter';
            elseif (stripos($ua, 'linkedin') !== false) $label = 'bot:linkedin';
            elseif (stripos($ua, 'slack') !== false) $label = 'bot:slack';
            elseif (stripos($ua, 'discord') !== false) $label = 'bot:discord';

            return [
                'source'        => $label,
                'referer_raw'   => $referer,
                'host_referrer' => $host,
                'user_agent'    => $ua,
            ];
        }

        if ($host) {
            $mapped = self::mapHostToSource($host);
            return [
                'source'        => $mapped ?? 'direct',
                'referer_raw'   => $referer,
                'host_referrer' => $host,
                'user_agent'    => $ua,
            ];
        }

        if (stripos($ua, 'FBAN') !== false || stripos($ua, 'FBAV') !== false || stripos($ua, 'FB_IAB') !== false) {
            $fallback = 'facebook-app';
        } elseif (stripos($ua, 'Instagram') !== false) {
            $fallback = 'instagram';
        } elseif (stripos($ua, 'Telegram') !== false) {
            $fallback = 'telegram';
        } elseif (stripos($ua, 'WhatsApp') !== false) {
            $fallback = 'whatsapp';
        } else {
            $fallback = 'direct';
        }

        return [
            'source'        => $fallback,
            'referer_raw'   => $referer,
            'host_referrer' => $host,
            'user_agent'    => $ua,
        ];
    }
}
