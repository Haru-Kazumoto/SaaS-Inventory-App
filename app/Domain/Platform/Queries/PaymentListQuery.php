<?php

namespace App\Domain\Platform\Queries;

use App\Domain\Platform\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PaymentListQuery
{
    /** @param array{q?: ?string, method?: ?string, organization_id?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Payment::query()
            ->with(['organization:id,name', 'invoice:id,number'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('reference', 'like', "%{$term}%")
                ->orWhereHas('invoice', fn ($i) => $i->where('number', 'like', "%{$term}%"))
                ->orWhereHas('organization', fn ($o) => $o->where('name', 'like', "%{$term}%"))))
            ->when($filters['method'] ?? null, fn ($q, $m) => $q->where('method', $m))
            ->when($filters['organization_id'] ?? null, fn ($q, $id) => $q->where('organization_id', $id))
            ->latest('paid_at')->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Payment $p) => self::row($p));
    }

    public static function row(Payment $p): array
    {
        return [
            'id' => $p->id,
            'invoice_id' => $p->invoice_id,
            'invoice_number' => $p->relationLoaded('invoice') ? $p->invoice?->number : null,
            'organization_id' => $p->organization_id,
            'organization' => $p->relationLoaded('organization') ? $p->organization?->name : null,
            'amount' => $p->amount,
            'method' => $p->method->value,
            'status' => $p->status->value,
            'provider' => $p->provider,
            'reference' => $p->reference,
            'notes' => $p->notes,
            'paid_at' => $p->paid_at->toIso8601String(),
        ];
    }
}
