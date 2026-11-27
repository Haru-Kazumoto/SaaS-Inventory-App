<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Platform\Exceptions\BillingException;
use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Models\Plan;
use App\Domain\Platform\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Mengganti/menetapkan paket organisasi.
 * Langganan berjalan dibatalkan, langganan baru dimulai hari ini.
 * Paket berbayar otomatis dibuatkan tagihan periode pertama (bila $issueInvoice).
 */
final class ChangeSubscription
{
    public function __construct(private IssueInvoice $issueInvoice) {}

    public function handle(
        Organization $organization,
        Plan $plan,
        BillingCycle $cycle,
        bool $issueInvoice = true,
        int $trialDays = 0,
    ): Subscription {
        if (! $plan->is_active) {
            throw new BillingException("Paket {$plan->name} sedang tidak aktif.");
        }

        return DB::transaction(function () use ($organization, $plan, $cycle, $issueInvoice, $trialDays) {
            $organization->subscriptions()
                ->whereIn('status', SubscriptionStatus::running())
                ->update(['status' => SubscriptionStatus::Cancelled, 'cancelled_at' => now()]);

            $start = today();
            $isTrial = $trialDays > 0;
            $end = $isTrial ? $start->copy()->addDays($trialDays - 1) : $cycle->periodEnd($start);

            $subscription = $organization->subscriptions()->create([
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'status' => $isTrial ? SubscriptionStatus::Trialing : SubscriptionStatus::Active,
                'price' => $plan->priceFor($cycle),
                'current_period_start' => $start,
                'current_period_end' => $end,
            ]);

            // Organisasi yang ditangguhkan tetap ditangguhkan sampai diaktifkan manual.
            if ($organization->status !== OrganizationStatus::Suspended) {
                $organization->update([
                    'status' => $isTrial ? OrganizationStatus::Trial : OrganizationStatus::Active,
                    'trial_ends_at' => $isTrial ? $end->copy()->endOfDay() : null,
                ]);
            }

            if (! $isTrial && $issueInvoice && $subscription->price > 0) {
                $this->issueInvoice->forSubscription($subscription);
            }

            return $subscription;
        });
    }
}
