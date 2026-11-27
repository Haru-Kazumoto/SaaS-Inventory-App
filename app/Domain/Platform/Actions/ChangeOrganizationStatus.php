<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Platform\Enums\SubscriptionStatus;
use App\Domain\Organization\Models\Organization;
use Illuminate\Support\Facades\DB;

/** Tangguhkan, aktifkan kembali, atau hentikan organisasi. */
final class ChangeOrganizationStatus
{
    public function handle(Organization $organization, OrganizationStatus $status, ?string $reason = null): Organization
    {
        return DB::transaction(function () use ($organization, $status, $reason) {
            $notes = $organization->notes;
            if ($reason) {
                $stamp = now()->translatedFormat('d M Y H:i');
                $notes = trim(($notes ? $notes."\n" : '')."[{$stamp}] {$status->label()}: {$reason}");
            }

            $organization->update([
                'status' => $status,
                'suspended_at' => $status === OrganizationStatus::Suspended ? now() : null,
                'notes' => $notes,
            ]);

            if ($status === OrganizationStatus::Cancelled) {
                $organization->subscriptions()
                    ->whereIn('status', SubscriptionStatus::running())
                    ->update(['status' => SubscriptionStatus::Cancelled, 'cancelled_at' => now()]);
            }

            return $organization;
        });
    }
}
