<?php

namespace App\Domain\Platform\Queries;

use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Platform\Enums\PaymentStatus;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Models\Payment;
use App\Domain\Platform\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class OrganizationDetailQuery
{
    public function get(Organization $organization): array
    {
        $organization->load('currentSubscription.plan');
        $sub = $organization->currentSubscription;

        return [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'template' => $organization->template->value,
                'status' => $organization->status->value,
                'owner_name' => $organization->owner_name,
                'owner_email' => $organization->owner_email,
                'phone' => $organization->phone,
                'address' => $organization->address,
                'notes' => $organization->notes,
                'trial_ends_at' => $organization->trial_ends_at?->toIso8601String(),
                'suspended_at' => $organization->suspended_at?->toIso8601String(),
                'created_at' => $organization->created_at?->toIso8601String(),
            ],
            'subscription' => $sub ? [
                'id' => $sub->id,
                'status' => $sub->status->value,
                'billing_cycle' => $sub->billing_cycle->value,
                'price' => $sub->price,
                'period_start' => $sub->current_period_start->toDateString(),
                'period_end' => $sub->current_period_end->toDateString(),
                'days_left' => (int) today()->diffInDays($sub->current_period_end, false),
                'plan' => [
                    'id' => $sub->plan->id,
                    'name' => $sub->plan->name,
                    'max_units' => $sub->plan->max_units,
                    'max_users' => $sub->plan->max_users,
                    'max_products' => $sub->plan->max_products,
                ],
            ] : null,
            'usage' => [
                'users' => $organization->users()->count(),
                'units' => $organization->units()->withoutGlobalScopes()->count(),
                'products' => DB::table('products')
                    ->where('organization_id', $organization->id)->whereNull('deleted_at')->count(),
            ],
            'billing' => [
                'outstanding' => (int) $organization->invoices()->where('status', InvoiceStatus::Unpaid)->sum('total'),
                'paid_total' => (int) $organization->payments()->where('status', PaymentStatus::Success)->sum('amount'),
            ],
            'users' => $organization->users()
                ->with('units:id,name')
                ->orderBy('fullname')
                ->get()
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'fullname' => $u->fullname,
                    'username' => $u->username,
                    'email' => $u->email,
                    'organization_role' => $u->organization_role?->value,
                    'role' => $u->organization_role?->label() ?? '-',
                    'units' => $u->units->pluck('name')->all(),
                ]),
            'units' => $organization->units()
                ->withoutGlobalScopes()
                ->withCount('members')
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(fn ($unit) => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'code' => $unit->code,
                    'members_count' => $unit->members_count,
                ]),
            'terms' => $organization->terms(),
            'invoices' => $organization->invoices()
                ->withSum(['payments as paid' => fn ($q) => $q->where('status', PaymentStatus::Success)], 'amount')
                ->latest('issued_at')->latest('id')
                ->limit(20)
                ->get()
                ->map(fn (Invoice $i) => InvoiceListQuery::row($i)),
            'subscriptions' => $organization->subscriptions()
                ->with('plan:id,name')
                ->latest('id')
                ->limit(10)
                ->get()
                ->map(fn (Subscription $s) => [
                    'id' => $s->id,
                    'plan' => $s->plan?->name,
                    'status' => $s->status->value,
                    'billing_cycle' => $s->billing_cycle->value,
                    'price' => $s->price,
                    'period_start' => $s->current_period_start->toDateString(),
                    'period_end' => $s->current_period_end->toDateString(),
                ]),
            'payments' => $organization->payments()
                ->with('invoice:id,number')
                ->latest('paid_at')
                ->limit(10)
                ->get()
                ->map(fn (Payment $p) => PaymentListQuery::row($p)),
        ];
    }
}
