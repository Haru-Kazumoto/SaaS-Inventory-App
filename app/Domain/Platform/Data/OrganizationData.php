<?php

namespace App\Domain\Platform\Data;

use App\Domain\Platform\Enums\BillingCycle;
use App\Domain\Organization\Enums\OrganizationTemplate;

final readonly class OrganizationData
{
    public function __construct(
        public string $name,
        public OrganizationTemplate $template,
        public ?string $ownerName = null,
        public ?string $ownerEmail = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $notes = null,
        // hanya dipakai saat membuat organisasi baru
        public ?int $planId = null,
        public BillingCycle $billingCycle = BillingCycle::Monthly,
        public int $trialDays = 0,
    ) {}
}
