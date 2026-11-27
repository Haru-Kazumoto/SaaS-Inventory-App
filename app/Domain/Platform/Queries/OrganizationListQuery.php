<?php

namespace App\Domain\Platform\Queries;

use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Organization\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class OrganizationListQuery
{
    /** @param array{q?: ?string, status?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Organization::query()
            ->withCount('users')
            ->with('currentSubscription.plan:id,name')
            ->withSum(['invoices as outstanding' => fn ($q) => $q->where('status', InvoiceStatus::Unpaid)], 'total')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('owner_name', 'like', "%{$term}%")
                ->orWhere('owner_email', 'like', "%{$term}%")))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Organization $o) => [
                'id' => $o->id,
                'name' => $o->name,
                'slug' => $o->slug,
                'template' => $o->template->value,
                'status' => $o->status->value,
                'owner_name' => $o->owner_name,
                'owner_email' => $o->owner_email,
                'users_count' => $o->users_count,
                'plan' => $o->currentSubscription?->plan?->name,
                'billing_cycle' => $o->currentSubscription?->billing_cycle?->value,
                'period_end' => $o->currentSubscription?->current_period_end?->toDateString(),
                'outstanding' => (int) ($o->outstanding ?? 0),
                'created_at' => $o->created_at?->toIso8601String(),
            ]);
    }

    /** Opsi ringkas untuk dropdown. */
    public function options(): array
    {
        return Organization::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn ($o) => ['label' => $o->name, 'value' => $o->id])->all();
    }
}
