<?php

namespace App\Domain\Platform\Models;

use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Enums\InvoiceStatus;
use App\Domain\Platform\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'number', 'organization_id', 'subscription_id', 'status', 'subtotal', 'discount',
        'tax', 'total', 'period_start', 'period_end', 'issued_at', 'due_at', 'paid_at',
        'voided_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tax' => 'integer',
            'total' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function amountPaid(): int
    {
        return (int) $this->payments()->where('status', PaymentStatus::Success)->sum('amount');
    }

    /** Status untuk tampilan: unpaid yang lewat jatuh tempo menjadi "overdue". */
    public function displayStatus(): string
    {
        if ($this->status === InvoiceStatus::Unpaid && $this->due_at->lt(today())) {
            return 'overdue';
        }

        return $this->status->value;
    }
}
