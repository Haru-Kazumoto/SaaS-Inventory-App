<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Actions\SavePlan;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Models\Plan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\SavePlanRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Platform/Plans/Index', [
            'plans' => Plan::query()
                ->withCount(['subscriptions as subscribers' => fn ($q) => $q->whereIn('status', SubscriptionStatus::running())])
                ->orderBy('sort_order')->orderBy('id')
                ->get(),
        ]);
    }

    public function store(SavePlanRequest $request, SavePlan $save): RedirectResponse
    {
        $plan = $save->handle($request->toData());

        return back()->with('success', "Paket {$plan->name} dibuat");
    }

    public function update(SavePlanRequest $request, Plan $plan, SavePlan $save): RedirectResponse
    {
        $save->handle($request->toData(), $plan);

        return back()->with('success', "Paket {$plan->name} diperbarui");
    }
}
