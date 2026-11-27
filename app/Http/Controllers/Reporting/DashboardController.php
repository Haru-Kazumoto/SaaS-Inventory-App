<?php

namespace App\Http\Controllers\Reporting;

use App\Domain\Organization\Support\Access;
use App\Domain\Reporting\Queries\DashboardQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardQuery $query): Response|RedirectResponse
    {
        if ($request->user()->isPlatformAdmin()) {
            return redirect()->route('platform.dashboard');
        }

        if (Access::managesOrganization()) {
            return Inertia::render('DashboardOrganization', $query->organization());
        }

        return Inertia::render('Dashboard', $query->operational((int) $request->input('period', now()->year)));
    }
}
