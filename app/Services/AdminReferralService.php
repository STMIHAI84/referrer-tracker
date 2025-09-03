<?php

namespace App\Services;

use App\Models\Referral;
use App\Support\ReferralFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReferralService
{
    /** Construieste query-ul pe baza filtrelor (reutilizabil pentru listare + export) */
    public function buildQuery(ReferralFilters $f,bool $withOrdering = true): Builder
    {
        $query = Referral::query()
            ->when($f->source, fn($q,$v) => $q->fromSource($v))
            ->when($f->utm_source, fn($q,$v) => $q->withUtm($v))
            ->excludeBots($f->exclude_bots)
            ->between($f->from, $f->to)
            ->search($f->q);

        if ($withOrdering) {
            $query->latest();
        }

        return $query;

    }

    /** Listare cu paginare */
    public function paginate(ReferralFilters $f): LengthAwarePaginator
    {
        return $this->buildQuery($f)->paginate($f->per_page)->withQueryString();
    }

    /** Valorile distincte pentru filtre */
    public function filterOptions(): array
    {
        return [
            'sources'    => Referral::select('source')->distinct()->orderBy('source')->pluck('source'),
            'utmSources' => Referral::select('utm_source')->whereNotNull('utm_source')->distinct()->orderBy('utm_source')->pluck('utm_source'),
        ];
    }

    /** Statistici aggregate (pe sursă) */
    public function totalsBySource(ReferralFilters $f)
    {
        return $this->buildQuery($f,false)
            ->selectRaw('source, COUNT(*) as c')
            ->groupBy('source')
            ->orderByDesc('c')
            ->pluck('c', 'source');
    }

    /** Export CSV ca stream (respectă filtrele curente) */
    public function exportCsv(ReferralFilters $f): StreamedResponse
    {
        $query = $this->buildQuery($f);
        $filename = 'referrals-'.now()->format('Y-m-d_H-i').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');


            fwrite($out, "\xEF\xBB\xBF");


            fputcsv($out, [
                'ID','Data','Sursa',
                'UTM Source','UTM Medium','UTM Campaign',
                'Host','Landing','IP','User Agent'
            ]);


            $query->chunkById(1000, function ($chunk) use ($out) {
                foreach ($chunk as $r) {
                    fputcsv($out, [
                        $r->id,
                        optional($r->created_at)->format('Y-m-d H:i:s'),
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
                fflush($out);
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
