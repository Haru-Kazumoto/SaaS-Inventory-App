<?php

namespace App\Domain\Organization\Support;

use App\Domain\Organization\Models\Organization;
use Illuminate\Support\Collection;

/** Akses ringkas ke OrganizationContext dari mana saja. */
final class CurrentOrganization
{
    public static function context(): OrganizationContext
    {
        return app(OrganizationContext::class);
    }

    public static function get(): Organization
    {
        return self::context()->organization();
    }

    public static function id(): int
    {
        return self::context()->id();
    }

    public static function has(): bool
    {
        return self::context()->has();
    }

    public static function units(): Collection
    {
        return self::context()->accessibleUnits();
    }

    /** @return int[] */
    public static function unitIds(): array
    {
        return self::context()->accessibleUnitIds();
    }
}
