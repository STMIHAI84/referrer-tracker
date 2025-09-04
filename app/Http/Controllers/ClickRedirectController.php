<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClickRedirectController extends Controller
{
    public function handle(Request $req)
    {
        $to       = $req->query('to', route('landing'));
        $clid     = $req->query('_sdclid');
        $sdSource = strtolower($req->query('sd_source', 'direct'));

        // Validare semnătură
        $data = $req->only(['to','_sdclid','sd_source','utm_source','utm_medium','utm_campaign','utm_term','utm_content']);
        ksort($data);
        $expected = hash_hmac('sha256', http_build_query($data, '', '&', PHP_QUERY_RFC3986), config('app.key'));
        if (!hash_equals($expected, (string) $req->query('sig'))) {
            abort(403, 'Invalid signature'); // protecție contra manipulării linkului
        }

        // Log click (opțional)
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
            // nu bloca redirectul dacă DB pică
        }

        // Setează cookie first-party (persistă sursa și fără referer)
        $resp = redirect()->away($to);
        $resp->withCookie(cookie()->make('_sdclid', $clid, 60*24*30, '/', null, false, false));
        $resp->withCookie(cookie()->make('sd_source', $sdSource, 60*24*30, '/', null, false, false));
        return $resp;
    }
}
