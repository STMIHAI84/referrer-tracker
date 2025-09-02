<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'source'     => ['nullable','string','max:100'],
            'utm_source' => ['nullable','string','max:100'],
            'q'          => ['nullable','string','max:200'],
            'from'       => ['nullable','date'],
            'to'         => ['nullable','date'],
            'exclude_bots' => ['nullable','boolean'],
        ]);

        $query = Referral::query()
            ->when($validated['source'] ?? null, fn($q,$v) => $q->fromSource($v))
            ->when($validated['utm_source'] ?? null, fn($q,$v) => $q->withUtm($v))
            ->excludeBots((bool)($validated['exclude_bots'] ?? false))
            ->between($validated['from'] ?? null, $validated['to'] ?? null)
            ->search($validated['q'] ?? null)
            ->latest();

        // Paginare (mai bine decât limit fix)
        $items = $query->paginate(50)->withQueryString();

        // Statistici (agregări în DB, nu pe collection)
        $sources = Referral::select('source')->distinct()->orderBy('source')->pluck('source');
        $utmSources = Referral::select('utm_source')->whereNotNull('utm_source')->distinct()->orderBy('utm_source')->pluck('utm_source');

        $totalsBySource = Referral::selectRaw('source, COUNT(*) as c')
            ->groupBy('source')->orderByDesc('c')->pluck('c','source');

        return view('admin.referrers', compact('items','sources','utmSources','totalsBySource'));
    }

    public function export(Request $request): StreamedResponse
    {
        // Folosește aceleași filtre ca în index
        $validated = $request->validate([
            'source'     => ['nullable','string','max:100'],
            'utm_source' => ['nullable','string','max:100'],
            'q'          => ['nullable','string','max:200'],
            'from'       => ['nullable','date'],
            'to'         => ['nullable','date'],
            'exclude_bots' => ['nullable','boolean'],
        ]);

        $query = Referral::query()
            ->when($validated['source'] ?? null, fn($q,$v) => $q->fromSource($v))
            ->when($validated['utm_source'] ?? null, fn($q,$v) => $q->withUtm($v))
            ->excludeBots((bool)($validated['exclude_bots'] ?? false))
            ->between($validated['from'] ?? null, $validated['to'] ?? null)
            ->search($validated['q'] ?? null)
            ->latest();

        $filename = 'referrals-'.now()->format('Y-m-d_H-i').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // BOM pentru Excel
            fwrite($out, "\xEF\xBB\xBF");

            // Header
            fputcsv($out, ['ID','Data','Sursa','UTM Source','UTM Medium','UTM Campaign','Host','Landing','IP','User Agent']);

            // Stream pe loturi, fără să încărcăm tot în memorie
            $query->chunkById(1000, function ($chunk) use ($out) {
                foreach ($chunk as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->created_at?->format('Y-m-d H:i:s'),
                        $r->source,
                        $r->utm_source,
                        $r->utm_medium,
                        $r->utm_campaign,
                        $r->referrer_host,
                        $r->landing_path,
                        $r->ip,
                        $r->user_agent,
                    ]);
                }
                // forțează flush
                fflush($out);
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
