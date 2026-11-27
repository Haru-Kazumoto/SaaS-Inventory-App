<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Platform\Data\InvoiceLineData;
use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Platform\Exceptions\BillingException;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Models\Subscription;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class IssueInvoice
{
    /**
     * @param  InvoiceLineData[]  $lines
     */
    public function handle(
        Organization $organization,
        array $lines,
        int $dueInDays = 7,
        int $discount = 0,
        int $tax = 0,
        ?string $notes = null,
        ?Subscription $subscription = null,
        ?CarbonInterface $periodStart = null,
        ?CarbonInterface $periodEnd = null,
    ): Invoice {
        if ($lines === []) {
            throw new BillingException('Tagihan minimal berisi satu item.');
        }

        $subtotal = array_sum(array_map(fn (InvoiceLineData $l) => $l->amount(), $lines));

        if ($discount > $subtotal) {
            throw new BillingException('Diskon tidak boleh melebihi subtotal.');
        }

        return DB::transaction(function () use (
            $organization, $lines, $dueInDays, $discount, $tax, $notes, $subscription, $periodStart, $periodEnd, $subtotal
        ) {
            $invoice = Invoice::create([
                'number' => $this->nextNumber(),
                'organization_id' => $organization->id,
                'subscription_id' => $subscription?->id,
                'status' => InvoiceStatus::Unpaid,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $subtotal - $discount + $tax,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'issued_at' => today(),
                'due_at' => today()->addDays($dueInDays),
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                $invoice->items()->create([
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'amount' => $line->amount(),
                ]);
            }

            return $invoice;
        });
    }

    /** Tagihan untuk satu periode langganan. */
    public function forSubscription(Subscription $subscription, int $dueInDays = 7): Invoice
    {
        $subscription->loadMissing('plan', 'organization');

        $description = sprintf(
            'Langganan paket %s (%s) %s – %s',
            $subscription->plan->name,
            $subscription->billing_cycle->label(),
            $subscription->current_period_start->translatedFormat('d M Y'),
            $subscription->current_period_end->translatedFormat('d M Y'),
        );

        return $this->handle(
            organization: $subscription->organization,
            lines: [new InvoiceLineData($description, 1, $subscription->price)],
            dueInDays: $dueInDays,
            subscription: $subscription,
            periodStart: $subscription->current_period_start,
            periodEnd: $subscription->current_period_end,
        );
    }

    /** Nomor berurutan per bulan: INV-202611-0001. Dipanggil di dalam transaksi. */
    private function nextNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';

        $last = Invoice::where('number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('number')
            ->value('number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
