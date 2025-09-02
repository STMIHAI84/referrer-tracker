<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $query = Referral::query();

        // Filtrare după sursă
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        // Filtrare după UTM source
        if ($request->has('utm_source')) {
            $query->where('utm_source', $request->utm_source);
        }

        $items = $query->latest()->limit(200)->get();

        // Statistici pentru filtre
        $sources = Referral::select('source')->distinct()->pluck('source');
        $utmSources = Referral::select('utm_source')->distinct()->pluck('utm_source');

        return view('admin.referrers', compact('items', 'sources', 'utmSources'));
    }

    public function export()
    {
        $referrals = Referral::all();

        return response()->streamDownload(function () use ($referrals) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['ID', 'Data', 'Sursa', 'UTM Source', 'UTM Medium', 'UTM Campaign', 'Host', 'IP', 'User Agent']);

            // Date
            foreach ($referrals as $referral) {
                fputcsv($handle, [
                    $referral->id,
                    $referral->created_at,
                    $referral->source,
                    $referral->utm_source,
                    $referral->utm_medium,
                    $referral->utm_campaign,
                    $referral->referrer_host,
                    $referral->ip,
                    $referral->user_agent
                ]);
            }

            fclose($handle);
        }, 'referrals-' . date('Y-m-d') . '.csv');
    }
}
