<?php

namespace App\Domain\Organization\Models;

use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Organization\Enums\OrganizationTemplate;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Models\Invoice;
use App\Domain\Platform\Models\Payment;
use App\Domain\Platform\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Tenant. Model ini sendiri TIDAK memakai BelongsToOrganization. */
class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'template', 'status', 'settings', 'owner_name', 'owner_email',
        'phone', 'address', 'trial_ends_at', 'suspended_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'template' => OrganizationTemplate::class,
            'status' => OrganizationStatus::class,
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    /** Organisasi boleh dipakai (login & bekerja). */
    public function isUsable(): bool
    {
        return ! in_array($this->status, [OrganizationStatus::Suspended, OrganizationStatus::Cancelled], true);
    }

    /** Terminologi efektif: bawaan template + override di settings.terms. */
    public function terms(): array
    {
        return array_merge($this->template->defaultTerms(), $this->settings['terms'] ?? []);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** Langganan yang sedang berjalan (trialing/active/past_due), paling baru. */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->ofMany(['id' => 'max'], fn ($q) => $q->whereIn('status', SubscriptionStatus::running()));
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
