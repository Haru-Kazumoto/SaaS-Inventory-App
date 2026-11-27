<?php

namespace App\Domain\Organization\Support;

use Illuminate\Database\Query\Builder;

/**
 * Pembatas tenant untuk Query Builder (DB::table), yang tidak tersentuh global scope Eloquent.
 *
 *   DB::table('products as p')->tap(fn ($q) => OrgScope::apply($q, 'p'))
 *   OrgScope::units($q, 'p.unit_id', $unitId)   // unit tertentu, atau semua unit yang boleh diakses
 *
 * Melempar LogicException bila konteks organisasi belum ada, supaya query tanpa scope tidak lolos diam-diam.
 */
final class OrgScope
{
    public static function apply(Builder $query, string $alias): Builder
    {
        return $query->where("{$alias}.organization_id", CurrentOrganization::id());
    }

    /**
     * Batasi ke unit: bila $unitId diberikan, harus termasuk unit yang boleh diakses;
     * bila null, ke semua unit yang boleh diakses user.
     */
    public static function units(Builder $query, string $column, ?int $unitId = null): Builder
    {
        $allowed = CurrentOrganization::unitIds();

        if ($unitId !== null) {
            return $query->where($column, in_array($unitId, $allowed, true) ? $unitId : 0);
        }

        return $query->whereIn($column, $allowed ?: [0]);
    }
}
