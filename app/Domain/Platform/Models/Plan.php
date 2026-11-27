<?php

namespace App\Domain\Platform\Models;

use App\Domain\Platform\Enums\BillingCycle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'price_monthly', 'price_yearly',
        'max_units', 'max_users', 'max_products', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'price_yearly' => 'integer',
            'max_units' => 'integer',
            'max_users' => 'integer',
            'max_products' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function priceFor(BillingCycle $cycle): int
    {
        return $cycle === BillingCycle::Monthly ? $this->price_monthly : $this->price_yearly;
    }
}
