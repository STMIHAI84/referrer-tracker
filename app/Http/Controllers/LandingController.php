<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    public function show(Request $request): View
    {
        $referral = $this->referralService->trackReferral($request);
        $message = $this->referralService->getTrackingMessage($referral);

        return view('landing', [
            'message' => $message,
            'entry' => $referral, // Păstrăm variabila 'entry' pentru compatibilitate
            'queryParams' => $request->query()
        ]);
    }
}
