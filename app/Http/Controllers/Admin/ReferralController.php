<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReferralFiltersRequest;
use App\Services\AdminReferralService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralController extends Controller
{
    public function __construct(private readonly AdminReferralService $service) {}

    public function index(ReferralFiltersRequest $request)
    {
        $filters = $request->toDto();
        return view('admin.referrers', [
            'items'          => $this->service->paginate($filters),
            ...$this->service->filterOptions(),
            'totalsBySource' => $this->service->totalsBySource($filters),
        ]);
    }

    public function export(ReferralFiltersRequest $request): StreamedResponse
    {
        $filters = $request->toDto(perPage: 50);
        return $this->service->exportCsv($filters);
    }
}
