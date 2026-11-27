<?php

namespace App\Domain\Platform\Models;

use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Enums\PaymentMethod;
use App\Domain\Platform\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'organization_id', 'amount', 'method', 'status', 'provider',
        'reference', 'paid_at', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
