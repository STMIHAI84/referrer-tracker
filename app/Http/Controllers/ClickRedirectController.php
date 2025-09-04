<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClickRedirectController extends Controller
{
    public function handle(Request $req)
    {
        // 1) citesc parametrii QUERY așa cum au fost trimiși
        $to        = $req->query('to', route('landing'));
        $rawClid   = $req->query('_sdclid'); // pentru HMAC folosim fix ce e în URL
        $clid      = $rawClid ?: (string) Str::uuid(); // dar pentru cookie/log putem genera dacă lipsește
        $sdSource  = strtolower($req->query('sd_source', 'direct'));

        // 2) anti open-redirect (înainte de orice altceva)
        $hostOk = parse_url($to, PHP_URL_HOST) === parse_url(config('app.url'), PHP_URL_HOST);
        if (!$hostOk) {
            abort(400, 'Invalid target');
        }

        // 3) validare semnătură (must mirror TrackingLink::canonical)
        //    includ DOAR cheile non-nule/ne-goale și în aceeași formă
        $data = array_filter([
            'to'           => $to,
            '_sdclid'      => $rawClid, // atenție: nu $clid generat
            'sd_source'    => $sdSource,
            'utm_source'   => $req->query('utm_source'),
            'utm_medium'   => $req->query('utm_medium'),
            'utm_campaign' => $req->query('utm_campaign'),
            'utm_term'     => $req->query('utm_term'),
            'utm_content'  => $req->query('utm_content'),
        ], fn($v) => $v !== null && $v !== '');

        ksort($data); // canonical order
        $expected = hash_hmac(
            'sha256',
            http_build_query($data, '', '&', PHP_QUERY_RFC3986),
            config('app.key')
        );

        if (!hash_equals($expected, (string) $req->query('sig'))) {
            abort(403, 'Invalid signature');
        }


        try {
            DB::table('sd_clicks')->insert([
                'clid'         => $clid,
                'source'       => $sdSource,
                'utm_source'   => $req->query('utm_source'),
                'utm_medium'   => $req->query('utm_medium'),
                'utm_campaign' => $req->query('utm_campaign'),
                'full_url'     => $req->fullUrl(),
                'ip'           => $req->ip(),
                'user_agent'   => (string) $req->userAgent(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow
        }

        // 5) setează cookie-uri first-party și redirect
        $isSecure = app()->environment('production');
        $resp = redirect()->away($to);
        $resp->withCookie(cookie()->make('_sdclid', $clid, 60*24*30, '/', null, $isSecure, true));
        $resp->withCookie(cookie()->make('sd_source', $sdSource, 60*24*30, '/', null, $isSecure, true));
        return $resp;
    }
}
