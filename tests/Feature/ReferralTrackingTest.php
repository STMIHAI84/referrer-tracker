<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Request;

class ReferralTrackingTest extends TestCase
{
    public function test_external_referrer_is_tracked()
    {
        $request = Request::create('/landing', 'GET');
        $request->headers->set('referer', 'https://facebook.com');

        $service = new \App\Services\ReferralService();
        $referral = $service->trackReferral($request);

        $this->assertNotNull($referral);
        $this->assertEquals('facebook.com', $referral->referrer_host);
    }

    public function test_internal_referrer_is_not_tracked()
    {
        $request = Request::create('/landing', 'GET');
        $request->headers->set('referer', config('app.url'));

        $service = new \App\Services\ReferralService();
        $referral = $service->trackReferral($request);

        $this->assertNull($referral);
    }
}
