<?php

namespace App\Domain\Platform\Queries;

use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Platform\Enums\PaymentStatus;
use App\Domain\Platform\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InvoiceListQuery
{
    /** @param array{q?: ?string, status?: ?string, organization_id?: ?string} $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('organization:id,name')
            ->withSum(['payments as paid' => fn ($q) => $q->where('status', PaymentStatus::Success)], 'amount')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('number', 'like', "%{$term}%")
                ->orWhereHas('organization', fn ($o) => $o->where('name', 'like', "%{$term}%"))))
            ->when($filters['organization_id'] ?? null, fn ($q, $id) => $q->where('organization_id', $id))
            ->when($filters['status'] ?? null, function ($q, $status) {
                match ($status) {
                    'overdue' => $q->where('status', InvoiceStatus::Unpaid)->whereDate('due_at', '<', today()),
                    'unpaid' => $q->where('status', InvoiceStatus::Unpaid)->whereDate('due_at', '>=', today()),
                    default => $q->where('status', $status),
                };
            })
            ->latest('issued_at')->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Invoice $i) => self::row($i));
    }

    public static function row(Invoice $i): array
    {
        $paid = (int) ($i->paid ?? 0);

        return [
            'id' => $i->id,
            'number' => $i->number,
            'organization_id' => $i->organization_id,
            'organization' => $i->relationLoaded('organization') ? $i->organization?->name : null,
            'status' => $i->status->value,
            'display_status' => $i->displayStatus(),
            'total' => $i->total,
            'paid' => $paid,
            'remaining' => max(0, $i->total - $paid),
            'issued_at' => $i->issued_at->toDateString(),
            'due_at' => $i->due_at->toDateString(),
            'paid_at' => $i->paid_at?->toIso8601String(),
        ];
    }

    public function detail(Invoice $invoice): array
    {
        $invoice->load([
            'organization',
            'subscription.plan:id,name',
            'items',
            'payments' => fn ($q) => $q->latest('paid_at'),
            'payments.recorder:id,fullname',
        ]);
        $paid = $invoice->amountPaid();

        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'status' => $invoice->status->value,
            'display_status' => $invoice->displayStatus(),
            'subtotal' => $invoice->subtotal,
            'discount' => $invoice->discount,
            'tax' => $invoice->tax,
            'total' => $invoice->total,
            'paid' => $paid,
            'remaining' => max(0, $invoice->total - $paid),
            'issued_at' => $invoice->issued_at->toDateString(),
            'due_at' => $invoice->due_at->toDateString(),
            'paid_at' => $invoice->paid_at?->toIso8601String(),
            'voided_at' => $invoice->voided_at?->toIso8601String(),
            'period_start' => $invoice->period_start?->toDateString(),
            'period_end' => $invoice->period_end?->toDateString(),
            'notes' => $invoice->notes,
            'plan' => $invoice->subscription?->plan?->name,
            'organization' => [
                'id' => $invoice->organization->id,
                'name' => $invoice->organization->name,
                'owner_name' => $invoice->organization->owner_name,
                'owner_email' => $invoice->organization->owner_email,
                'phone' => $invoice->organization->phone,
                'address' => $invoice->organization->address,
            ],
            'items' => $invoice->items->map(fn ($it) => [
                'id' => $it->id,
                'description' => $it->description,
                'quantity' => $it->quantity,
                'unit_price' => $it->unit_price,
                'amount' => $it->amount,
            ]),
            'payments' => $invoice->payments->map(fn ($p) => [
                ...PaymentListQuery::row($p),
                'recorded_by' => $p->recorder?->fullname,
            ]),
        ];
    }
}
