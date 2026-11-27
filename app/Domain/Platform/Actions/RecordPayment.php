<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Platform\Data\PaymentData;
use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Platform\Enums\PaymentStatus;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Exceptions\BillingException;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Platform\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Catat pembayaran (manual sekarang; webhook gateway nanti memakai Action yang sama).
 * Bila total terbayar >= total tagihan: tagihan lunas, langganan & organisasi yang menunggak aktif kembali.
 */
final class RecordPayment
{
    public function handle(Invoice $invoice, PaymentData $data, ?int $recordedBy = null): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $recordedBy) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === InvoiceStatus::Void) {
                throw new BillingException('Tagihan sudah dibatalkan, tidak bisa menerima pembayaran.');
            }
            if ($invoice->status === InvoiceStatus::Paid) {
                throw new BillingException('Tagihan sudah lunas.');
            }

            $remaining = $invoice->total - $invoice->amountPaid();
            if ($data->amount <= 0 || $data->amount > $remaining) {
                throw new BillingException('Jumlah pembayaran harus antara Rp 1 dan sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').');
            }

            $payment = $invoice->payments()->create([
                'organization_id' => $invoice->organization_id,
                'amount' => $data->amount,
                'method' => $data->method,
                'status' => PaymentStatus::Success,
                'provider' => $data->provider,
                'reference' => $data->reference,
                'paid_at' => $data->paidAt,
                'notes' => $data->notes,
                'recorded_by' => $recordedBy,
            ]);

            if ($data->amount === $remaining) {
                $invoice->update(['status' => InvoiceStatus::Paid, 'paid_at' => $data->paidAt]);

                $subscription = $invoice->subscription;
                if ($subscription?->status === SubscriptionStatus::PastDue) {
                    $subscription->update(['status' => SubscriptionStatus::Active]);
                }

                $organization = $invoice->organization;
                if ($organization->status === OrganizationStatus::PastDue) {
                    $organization->update(['status' => OrganizationStatus::Active]);
                }
            }

            return $payment;
        });
    }
}
