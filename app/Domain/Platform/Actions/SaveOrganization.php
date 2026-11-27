<?php

namespace App\Domain\Platform\Actions;

use App\Domain\Platform\Data\OrganizationData;
use App\Domain\Organization\Enums\OrganizationStatus;
use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Models\Unit;
use App\Domain\Platform\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SaveOrganization
{
    public function __construct(private ChangeSubscription $changeSubscription) {}

    /** Buat organisasi baru (bila $organization null) atau ubah profilnya. */
    public function handle(OrganizationData $data, ?Organization $organization = null): Organization
    {
        return DB::transaction(function () use ($data, $organization) {
            $attributes = [
                'name' => $data->name,
                'template' => $data->template,
                'owner_name' => $data->ownerName,
                'owner_email' => $data->ownerEmail,
                'phone' => $data->phone,
                'address' => $data->address,
                'notes' => $data->notes,
            ];

            if ($organization) {
                $organization->update($attributes);

                return $organization;
            }

            $organization = Organization::create([
                ...$attributes,
                'slug' => $this->uniqueSlug($data->name),
                'status' => OrganizationStatus::Trial,
            ]);

            // Setiap organisasi minimal punya satu unit agar langsung bisa dipakai.
            Unit::create([
                'organization_id' => $organization->id,
                'name' => 'Utama',
                'code' => 'UTAMA',
            ]);

            if ($data->planId) {
                $this->changeSubscription->handle(
                    organization: $organization,
                    plan: Plan::findOrFail($data->planId),
                    cycle: $data->billingCycle,
                    trialDays: $data->trialDays,
                );
            }

            return $organization->refresh();
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'organisasi';
        $slug = $base;
        $i = 2;

        while (Organization::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
