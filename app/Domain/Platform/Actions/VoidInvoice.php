<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Platform\Exceptions\BillingException;
use App\Domain\Platform\Models\Invoice;

final class VoidInvoice
{
    public function handle(Invoice $invoice, ?string $reason = null): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Unpaid) {
            throw new BillingException('Hanya tagihan yang belum dibayar yang bisa dibatalkan.');
        }
        if ($invoice->amountPaid() > 0) {
            throw new BillingException('Tagihan sudah menerima sebagian pembayaran, tidak bisa dibatalkan.');
        }

        $invoice->update([
            'status' => InvoiceStatus::Void,
            'voided_at' => now(),
            'notes' => trim(($invoice->notes ? $invoice->notes."\n" : '').($reason ? "Dibatalkan: {$reason}" : 'Dibatalkan')),
        ]);

        return $invoice;
    }
}
