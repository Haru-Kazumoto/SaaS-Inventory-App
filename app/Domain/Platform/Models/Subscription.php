<?php

namespace App\Domain\Platform\Models;

use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Platform\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'organization_id', 'plan_id', 'billing_cycle', 'status', 'price',
        'current_period_start', 'current_period_end', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'status' => SubscriptionStatus::class,
            'price' => 'integer',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
