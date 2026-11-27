<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Platform\Actions\ChangeOrganizationStatus;
use App\Domain\Platform\Actions\ChangeSubscription;
use App\Domain\Platform\Actions\CreateOrganizationOwner;
use App\Domain\Platform\Actions\SaveOrganization;
use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Models\Plan;
use App\Domain\Platform\Queries\OrganizationDetailQuery;
use App\Domain\Platform\Queries\OrganizationListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\ChangeOrganizationStatusRequest;
use App\Http\Requests\Platform\ChangeSubscriptionRequest;
use App\Http\Requests\Platform\CreateOrganizationOwnerRequest;
use App\Http\Requests\Platform\SaveOrganizationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request, OrganizationListQuery $query): Response
    {
        return Inertia::render('Platform/Organizations/Index', [
            'organizations' => $query->paginate($request->only(['q', 'status'])),
            'plans' => $this->planOptions(),
        ]);
    }

    public function store(SaveOrganizationRequest $request, SaveOrganization $save): RedirectResponse
    {
        $organization = $save->handle($request->toData());

        return redirect()
            ->route('platform.organizations.show', $organization)
            ->with('success', "Organisasi {$organization->name} berhasil dibuat");
    }

    public function show(Organization $organization, OrganizationDetailQuery $query): Response
    {
        return Inertia::render('Platform/Organizations/Show', [
            ...$query->get($organization),
            'plans' => $this->planOptions(),
        ]);
    }

    public function update(SaveOrganizationRequest $request, Organization $organization, SaveOrganization $save): RedirectResponse
    {
        $save->handle($request->toData(), $organization);

        return back()->with('success', 'Profil organisasi diperbarui');
    }

    public function updateStatus(
        ChangeOrganizationStatusRequest $request,
        Organization $organization,
        ChangeOrganizationStatus $change,
    ): RedirectResponse {
        $change->handle($organization, $request->status(), $request->input('reason'));

        return back()->with('success', "Status organisasi menjadi {$request->status()->label()}");
    }

    public function changeSubscription(
        ChangeSubscriptionRequest $request,
        Organization $organization,
        ChangeSubscription $change,
    ): RedirectResponse {
        $subscription = $change->handle(
            organization: $organization,
            plan: Plan::findOrFail($request->integer('plan_id')),
            cycle: BillingCycle::from($request->input('billing_cycle')),
            issueInvoice: $request->boolean('issue_invoice', true),
            trialDays: $request->integer('trial_days'),
        );

        return back()->with('success', "Paket diganti ke {$subscription->plan->name}");
    }

    public function createOwner(
        CreateOrganizationOwnerRequest $request,
        Organization $organization,
        CreateOrganizationOwner $create,
    ): RedirectResponse {
        [$user, $password] = $create->handle(
            $organization,
            $request->input('fullname'),
            $request->input('email'),
            $request->input('username'),
        );

        return back()
            ->with('success', "Akun pemilik {$user->fullname} (@{$user->username}) dibuat")
            ->with('generated_password', $password);
    }

    private function planOptions(): array
    {
        return Plan::query()->where('is_active', true)->orderBy('sort_order')
            ->get(['id', 'name', 'price_monthly', 'price_yearly'])
            ->toArray();
    }
}
