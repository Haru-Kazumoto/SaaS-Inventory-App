<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Queries\PlatformDashboardQuery;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(PlatformDashboardQuery $query): Response
    {
        return Inertia::render('Platform/Dashboard', $query->get());
    }
}
